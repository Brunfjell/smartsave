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
  
  # Convert timestamp to Date-Time format
  energy_data$timestamp <- as.POSIXct(energy_data$timestamp, format="%Y-%m-%d %H:%M:%S")
  env_data$timestamp <- as.POSIXct(env_data$timestamp, format="%Y-%m-%d %H:%M:%S")
  
  # Merge datasets (if environmental data is present)
  if (nrow(env_data) > 0) {
    merged_data <- left_join(energy_data, env_data, by = "timestamp")  # Left join to keep all energy data
  } else {
    merged_data <- energy_data  # Use energy data alone
    merged_data$temperature_celsius <- NA  # Add missing columns
    merged_data$humidity_percent <- NA
  }
  
  # Handle missing values
  merged_data <- na.omit(merged_data)  # Remove rows with NA values
  
  # Get last valid timestamp for prediction
  last_timestamp <- max(merged_data$timestamp, na.rm = TRUE)
  
  # Ensure last_timestamp is valid before using seq()
  if (is.na(last_timestamp) || !is.finite(last_timestamp)) {
    cat(sprintf("Skipping user_id: %d due to invalid timestamp.\n", user_id))
    next  # Skip user if timestamp is invalid
  }
  
  # Predict energy consumption for the next 24 hours
  future_timestamps <- seq(last_timestamp + hours(1), by = "hour", length.out = 24)
  
  if (nrow(merged_data) > 10 && !all(is.na(merged_data$temperature_celsius))) {
    # Train regression model if enough data is available
    model <- lm(energy_consumption_kwh ~ temperature_celsius + humidity_percent, data = merged_data)
    
    # Create future data frame using mean environmental values
    future_data <- data.frame(
      temperature_celsius = mean(merged_data$temperature_celsius, na.rm=TRUE),
      humidity_percent = mean(merged_data$humidity_percent, na.rm=TRUE)
    )
    
    # Predict energy consumption
    predictions <- predict(model, newdata = future_data)
    
    cat(sprintf("Regression-based forecasting for user_id: %d\n", user_id))
    
  } else {
    # Use mean energy consumption if no regression is possible
    mean_energy <- mean(merged_data$energy_consumption_kwh, na.rm=TRUE)
    predictions <- rep(mean_energy, 24)  # Assume future usage remains at the mean
    
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
