RewriteEngine on
RewriteCond $1 !^({#index_file#}\.php|images|resource|robots\.txt)
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^(.*)$ {#index_file#}.php/$1 [L]