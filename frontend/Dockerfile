# Use Node.js LTS version with Alpine Linux
FROM node:18-alpine

# Set working directory
WORKDIR /app

# Copy package files
COPY package*.json ./

# Install dependencies
RUN npm install

# Copy the rest of the application
COPY . .

# Expose port
EXPOSE 5173

# Start the development server
ENV HOST=0.0.0.0
CMD ["sh", "-c", "npx vite --host $HOST"] 