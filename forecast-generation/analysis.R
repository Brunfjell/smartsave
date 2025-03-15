# Load necessary libraries
library(DBI)
library(RMySQL)
library(dplyr)
library(lubridate)
library(forecast)

# Database connection
con <- dbConnect(
  RMySQL::MySQL(),
  dbname = "smartsave",
  host = "127.0.0.1",
  port = 3306,
  user = "root",
  password = ""
)

# Fetch distinct user IDs
user_query <- "SELECT DISTINCT user_id FROM energy_data"
user_ids <- dbGetQuery(con, user_query)$user_id

# Loop through each user_id
for (user_id in user_ids) {
  
  # Fetch energy data for the user
  energy_query <- sprintf(
    "SELECT timestamp, energy_consumption_kwh FROM energy_data WHERE user_id = %d ORDER BY timestamp ASC", 
    user_id
  )
  energy_data <- dbGetQuery(con, energy_query)
  
  # Fetch environmental data for the user
  env_query <- sprintf(
    "SELECT timestamp, temperature_celsius, humidity_percent FROM environmental_data WHERE user_id = %d ORDER BY timestamp ASC", 
    user_id
  )
  env_data <- dbGetQuery(con, env_query)
  
  # Check if energy data is available (mandatory)
  if (nrow(energy_data) == 0) {
    cat(sprintf("Skipping user_id: %d due to no energy data.\n", user_id))
    next  # Skip user if no energy data exists
  }
  
  # Convert timestamp to POSIXct format and handle parsing errors
  energy_data$timestamp <- as.POSIXct(energy_data$timestamp, format="%Y-%m-%d %H:%M:%S", tz="UTC")
  env_data$timestamp <- as.POSIXct(env_data$timestamp, format="%Y-%m-%d %H:%M:%S", tz="UTC")
  
  # Remove rows with completely invalid timestamps
  energy_data <- energy_data[!is.na(energy_data$timestamp) & is.finite(energy_data$timestamp), ]
  env_data <- env_data[!is.na(env_data$timestamp) & is.finite(env_data$timestamp), ]
  
  # Merge datasets (keep energy data even if environmental data is missing)
  if (nrow(env_data) > 0) {
    merged_data <- left_join(energy_data, env_data, by = "timestamp")
  } else {
    merged_data <- energy_data
    merged_data$temperature_celsius <- NA
    merged_data$humidity_percent <- NA
  }
  
  # Remove any remaining rows with NA timestamps
  merged_data <- merged_data[!is.na(merged_data$timestamp) & is.finite(merged_data$timestamp), ]
  
  # Get last valid timestamp
  last_timestamp <- max(merged_data$timestamp, na.rm = TRUE)
  
  # If last_timestamp is still invalid, use the current time as fallback
  if (is.na(last_timestamp) || !is.finite(last_timestamp)) {
    cat(sprintf("Warning: No valid timestamps for user_id: %d. Using current system time for predictions.\n", user_id))
    last_timestamp <- Sys.time()
  }
  
  # Generate future timestamps for predictions
  future_timestamps <- seq(last_timestamp + hours(1), by = "hour", length.out = 24)
  
  # Regression model (if enough data)
  if (nrow(merged_data) > 10 && !all(is.na(merged_data$temperature_celsius))) {
    model <- lm(energy_consumption_kwh ~ temperature_celsius + humidity_percent, data = merged_data)
    
    future_data <- data.frame(
      temperature_celsius = mean(merged_data$temperature_celsius, na.rm=TRUE),
      humidity_percent = mean(merged_data$humidity_percent, na.rm=TRUE)
    )
    
    predictions <- predict(model, newdata = future_data)
    cat(sprintf("Regression-based forecasting for user_id: %d\n", user_id))
    
  } else {
    # Mean-based fallback prediction
    mean_energy <- mean(merged_data$energy_consumption_kwh, na.rm=TRUE)
    predictions <- rep(mean_energy, 24)
    cat(sprintf("Using mean-based forecasting for user_id: %d due to limited data.\n", user_id))
  }
  
  # Prepare forecast data for database
  forecast_data <- data.frame(
    user_id = user_id,
    forecast_datetime = future_timestamps,
    predicted_energy_kwh = predictions,
    confidence_level = 0.95
  )
  
  # Save predictions to database
  dbWriteTable(con, "forecasts", forecast_data, append = TRUE, row.names = FALSE)
}

# Close database connection
dbDisconnect(con)
cat("All forecasting tasks completed and saved to the database.\n")
