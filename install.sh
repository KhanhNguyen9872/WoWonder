#!/usr/bin/bash
# Variable
rm -rf $HOME/.bash_history > /dev/null 2>&1
sudo="$(which sudo)"
apache2_conf="/etc/apache2/apache2.conf"
apache2_security="/etc/apache2/conf-available/security.conf"
mysql_conf="/etc/mysql/mariadb.conf.d/50-server.cnf"
phpmyadmin_conf="/etc/phpmyadmin/config-db.php"

# Function
function stop_mysql () {
	${sudo} service mysql stop > /dev/null 2>&1
	${sudo} killall mysqld > /dev/null 2>&1
}

# Main
if [[ -d /goorm/bin ]]; then
	${sudo} apt update -y
	${sudo} dpkg --configure -a
	${sudo} apt install p7zip-full unzip tar curl zip wget nano mesa-utils dialog php-gettext pv ffmpeg npm nodejs apache2 php mariadb-server phpmyadmin -y
	if [[ "$(cat /etc/hosts | grep -a -w -m1 '127.0.0.1 localhost')" == "" ]]; then
		${sudo} printf "\n127.0.0.1 localhost\n" >> /etc/hosts
	fi
	${sudo} rm -rf ${apache2_conf} > /dev/null 2>&1
	${sudo} cat > ${apache2_conf} << EOF
	ServerName 0.0.0.0
	DefaultRuntimeDir \${APACHE_RUN_DIR}
	PidFile \${APACHE_PID_FILE}
	Timeout 360
	KeepAlive On
	MaxKeepAliveRequests 100
	KeepAliveTimeout 5
	User \${APACHE_RUN_USER}
	Group \${APACHE_RUN_GROUP}
	HostnameLookups Off
	ErrorLog \${APACHE_LOG_DIR}/error.log
	LogLevel warn
	IncludeOptional mods-enabled/*.load
	IncludeOptional mods-enabled/*.conf
	Include ports.conf
	<Directory />
		Options FollowSymLinks
		AllowOverride None
		Require all denied
	</Directory>
	<Directory /usr/share>
			Options FollowSymLinks
		AllowOverride All
		Require all granted
	</Directory>
	<Directory /var/www/html>
		Options FollowSymLinks
		AllowOverride All
		Require all granted
	</Directory>
	AccessFileName .htaccess
	<FilesMatch "^\\.ht">
		Require all denied
	</FilesMatch>
	LogFormat "%v:%p %h %l %u %t \\"%r\\" %>s %O \\"%{Referer}i\\" \\"%{User-Agent}i\\"" vhost_combined
	LogFormat "%h %l %u %t \\"%r\\" %>s %O \\"%{Referer}i\\" \\"%{User-Agent}i\\"" combined
	LogFormat "%h %l %u %t \\"%r\\" %>s %O" common
	LogFormat "%{Referer}i -> %U" referer
	LogFormat "%{User-agent}i" agent
	IncludeOptional conf-enabled/*.conf
	IncludeOptional sites-enabled/*.conf
EOF
	${sudo} sed -i '/ServerTokens /d' ${apache2_security} > /dev/null 2>&1
	${sudo} sed -i '/ServerSignature /d' ${apache2_security} > /dev/null 2>&1
	${sudo} sed -i '1 a ServerTokens Prod' ${apache2_security} > /dev/null 2>&1
	${sudo} sed -i '2 a ServerSignature Off' ${apache2_security} > /dev/null 2>&1
	${sudo} sed -i 's/= 3306/= 3307/g' ${mysql_conf} > /dev/null 2>&1
        ${sudo} service mysql start > /dev/null 2>&1
	unset password
	while [[ "${password}" == "" ]]; do
		clear
		printf "\n\n SQL Username: wowonder"
		printf "\n\n New Password MySQL: "
		read password
	done
        mysql -u root << EOF
        DROP USER IF EXISTS 'wowonder'@'localhost';
        CREATE USER 'wowonder'@'localhost' IDENTIFIED BY "${password}";
        GRANT ALL PRIVILEGES ON *.* TO 'wowonder'@'localhost' IDENTIFIED BY "${password}";
        GRANT ALL PRIVILEGES ON *.* TO 'wowonder'@'%' IDENTIFIED BY "${password}";
        DROP USER IF EXISTS 'root'@'localhost';
        FLUSH PRIVILEGES;

EOF
	stop_mysql
	${sudo} a2enmod rewrite > /dev/null 2>&1
	printf "<?php\n\$dbuser='wowonder';\n\$dbpass=\"${password}\";\n\$basepath='';\n\$dbname='phpmyadmin';\n\$dbserver='localhost';\n\$dbport='3307';\n\$dbtype='mysql';\n" > ${phpmyadmin_conf}
	clear
	echo "Starting WoWonder...."
	${sudo} service apache2 start > /dev/null 2>&1
	sleep 1
	${sudo} service mysql start > /dev/null 2>&1
	${sudo} rm -rf *.sh *.md > /dev/null 2>&1
	echo "Done!"
else
       printf "\n\nNot a Goorm Ubuntu!\n"
fi
exit 0