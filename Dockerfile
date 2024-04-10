# Use the official Docker Library Drupal image
FROM drupal:10

# Remove the provided Drupal files
RUN rm -rf /opt/drupal/web

# Copy the composer.json file into the container
COPY composer.json ./

# Install composer
RUN composer update --no-scripts --no-autoloader

COPY . /opt/drupal

WORKDIR /opt/drupal