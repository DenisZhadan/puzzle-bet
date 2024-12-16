# Copying static files after start docker-compose

Copy static files from the Docker container 'bet-1.0.0' to the host directory '/var/www/static', use the following command:
```bash
docker cp bet-1.0.0:/var/www/static/. /var/www/static
```

# Apache
You need to activate ***<span style="color:red;">extension=pdo_pgsql</span>*** in php.ini

## Local configuration file:
```apacheconf

<VirtualHost *:80>
	ServerName Your.domain

	# Setting environment variables
	SetEnv DB_HOST localhost
	SetEnv DB_PORT 5432
	SetEnv DB_NAME YOUR-db-name
	SetEnv DB_USER postgres
	SetEnv DB_PASSWORD YOUR-password

	Header set X-Content-Type-Options "nosniff"

	<FilesMatch "\.(css|js|png|jpg|jpeg|gif|ico)$">
	    Header set Cache-Control "max-age=60, public"
	</FilesMatch>

	<FilesMatch "\.(php|html)$">
	    Header set Cache-Control "no-cache, no-store, must-revalidate"
	</FilesMatch>

	DocumentRoot "YOUR-project_path/www"
	<Directory "YOUR-project_path/www/">
		Options +Indexes +Includes +FollowSymLinks +MultiViews
		AllowOverride All
		Require local
	</Directory>
</VirtualHost>
```
