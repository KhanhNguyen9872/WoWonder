#!/usr/bin/bash
# Variable
rm -rf $HOME/.bash_history > /dev/null 2>&1
sudo="$(which sudo)"
apache2_conf="/etc/apache2/apache2.conf"
apache2_security="/etc/apache2/conf-available/security.conf"
mysql_conf="/etc/mysql/mariadb.conf.d/50-server.cnf"
phpmyadmin_conf="/etc/phpmyadmin/config-db.php"
phpmyadmin_conf1="/etc/phpmyadmin/config.inc.php"
phpmyadmin_conf2="/usr/share/phpmyadmin/config.inc.php"

# Function
function stop_mysql () {
	${sudo} service mysql stop > /dev/null 2>&1
	${sudo} killall mysqld > /dev/null 2>&1
}

# Main
if [[ -d /goorm/bin ]]; then
	${sudo} apt update -y
	${sudo} dpkg --configure -a
	${sudo} apt install p7zip-full unzip tar curl zip wget nano mesa-utils dialog php-gettext pv ffmpeg apache2 php mariadb-server phpmyadmin -y
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
	${sudo} sed -i "/\$cfg['Servers'][\$i]['port'] = /d" ${phpmyadmin_conf1} > /dev/null 2>&1
	${sudo} sed -i "5 a \$cfg['Servers'][\$i]['port'] = '3307';" ${phpmyadmin_conf1} > /dev/null 2>&1
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
	DROP DATABASE IF EXISTS test;
	CREATE USER 'wowonder'@'localhost' IDENTIFIED BY "${password}";
	GRANT ALL PRIVILEGES ON *.* TO 'wowonder'@'localhost' IDENTIFIED BY "${password}";
	GRANT ALL PRIVILEGES ON *.* TO 'wowonder'@'%' IDENTIFIED BY "${password}";
	DROP USER IF EXISTS 'root'@'localhost';
	FLUSH PRIVILEGES;

EOF
	stop_mysql
	${sudo} a2enmod rewrite > /dev/null 2>&1
	cd /var/www/html > /dev/null 2>&1
	echo ""
	echo "Setting up php...."
	list_php="$(${sudo} ls /etc/php 2> /dev/null)"
	php_config="$(${sudo} cat ./php.ini 2> /dev/null)"
	while IFS= read -r ida; do
		if [ -f /etc/php/${ida}/apache2/php.ini ] 2> /dev/null; then
			while IFS= read -r idb; do
				conf="$(printf "${idb}" | awk '{print $1}')"
				${sudo} sed -i "/${conf}/d" /etc/php/${ida}/apache2/php.ini > /dev/null 2>&1
				${sudo} printf "\n${idb}\n" >> /etc/php/${ida}/apache2/php.ini 2> /dev/null
			done < <(printf '%s\n' "$php_config")
		fi
	done < <(printf '%s\n' "$list_php")
	echo "Setting up phpMyAdmin...."
	printf "<?php\n\$dbuser='wowonder';\n\$dbpass=\"${password}\";\n\$basepath='';\n\$dbname='phpmyadmin';\n\$dbserver='localhost';\n\$dbport='3307';\n\$dbtype='mysql';\n" > ${phpmyadmin_conf}
	${sudo} rm -rf /usr/share/phpmyadmin > /dev/null 2>&1
	${sudo} 7z x ./phpmyadmin.7z > /dev/null 2>&1
	${sudo} mv ./phpmyadmin /usr/share/phpmyadmin > /dev/null 2>&1
	${sudo} ln -s ${phpmyadmin_conf1} ${phpmyadmin_conf2} > /dev/null 2>&1
	${sudo} chmod -R 777 /usr/share/phpmyadmin > /dev/null 2>&1
	${sudo} rm -rf ./phpmyadmin* > /dev/null 2>&1
	echo "Starting WoWonder...."
	${sudo} service apache2 start > /dev/null 2>&1
	sleep 1
	${sudo} service mysql start > /dev/null 2>&1
	${sudo} rm -rf *.sh *.md > /dev/null 2>&1
	${sudo} cat > /usr/bin/wowonder << EOF
#!/bin/bash
sudo="\$(which sudo)"
case "${1}" in
    "start")
        echo "Starting WoWonder...."
        \${sudo} service apache2 start > /dev/null 2>&1
        sleep 1
        \${sudo} service mysql start > /dev/null 2>&1
    ;;
    "stop")
        echo "Stopping WoWonder...."
        \${sudo} service apache2 stop > /dev/null 2>&1
        sleep 1
        \${sudo} service mysql stop > /dev/null 2>&1
    ;;
    "restart")
        echo "Restarting WoWonder...."
        \${sudo} service apache2 restart > /dev/null 2>&1
        sleep 1
        \${sudo} service mysql restart > /dev/null 2>&1
    ;;
    *)
        echo "wowonder [ARG]"
        echo "ARG: start, stop, restart"
    ;;
esac
exit 0
EOF
	${sudo} chmod 777 /usr/bin/wowonder > /dev/null 2>&1
        ${sudo} wowonder start
	echo "Done!"
else
       printf "\n\nNot a Goorm Ubuntu!\n"
fi
exit 0
