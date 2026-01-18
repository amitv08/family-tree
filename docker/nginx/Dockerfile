# Multi-stage build for Nginx with Family Tree
FROM nginx:alpine as base

# Install security updates
RUN apk update && apk upgrade && \
    apk add --no-cache curl && \
    rm -rf /var/cache/apk/*

# Create nginx user with specific UID/GID for consistency
RUN addgroup -g 101 -S nginx && \
    adduser -S -D -H -u 101 -h /var/cache/nginx -s /sbin/nologin -G nginx -g nginx nginx

# Copy nginx configuration
COPY docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf

# Create necessary directories
RUN mkdir -p /var/log/nginx /var/cache/nginx && \
    chown -R nginx:nginx /var/log/nginx /var/cache/nginx

# Copy static assets for caching
COPY assets/ /var/www/html/wp-content/plugins/family-tree/assets/

# Security hardening
RUN rm -rf /usr/share/nginx/html && \
    mkdir -p /usr/share/nginx/html && \
    chown -R nginx:nginx /usr/share/nginx/html

# Health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=5s --retries=3 \
    CMD curl -f http://localhost/health || exit 1

# Switch to non-root user
USER nginx

# Expose port 80
EXPOSE 80

# Default command
CMD ["nginx", "-g", "daemon off;"]