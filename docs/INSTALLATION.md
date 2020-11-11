```apacheconfig
<VirtualHost *:88>    
    ServerName lararocket.local
	
	DocumentRoot "E:\PROGRAM\Laradock\ttungbmt\WORDPRESS\lararocket\web"
	<Directory "E:\PROGRAM\Laradock\ttungbmt\WORDPRESS\lararocket\web">
        AllowOverride All
        Require all granted
	</Directory>
	
	# redirect to the URL without a trailing slash (uncomment if necessary)
	# RewriteRule ^/jet/$ /jet [L,R=301]
	Alias /jet "E:\PROGRAM\Laradock\ttungbmt\lararocket\laravel\public"	
	# prevent the directory redirect to the URL with a trailing slash
	# RewriteRule ^/jet$ /jet/ [L,PT]
	
	<Directory "E:\PROGRAM\Laradock\ttungbmt\lararocket\laravel\public">
        AllowOverride All
        Require all granted
	</Directory>
</VirtualHost>
```

Fix Flatsome
+ \themes\flatsome\inc\builder\core\server\src\Transformers\StringToArray.php, line 79
```$tag = isset($shortcode['tag']) ? $shortcode['tag'] : '';```


Easy Updates Manager
Admin Menu Editor
Table of Contents Plus
Advanced Editor Tools
Pretty Links
WooCommerce PDF Invoices & Packing Slips
Nextend Social Login and Register
ShortPixel Image Optimizer

Custom Sidebars – Dynamic Widget Area Manager


https://thachpham.com/wordpress/wp-plugin/them-tinh-thanh-viet-nam-va-toi-uu-lai-thong-tin-khach-hang-woocommerce.html
