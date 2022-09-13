# WoWonder
```bash
if [[ "$(which bash)" == "/data/data/com.termux/files/usr/bin/bash" ]]; then echo "Unsupported Termux!"; exit 1; else sudo="$(which sudo)"; ${sudo} rm -rf ./WoWonder; ${sudo} git clone https://KhanhNguyen9872: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@github.com/KhanhNguyen9872/WoWonder.git || exit 1 && {
	${sudo} rm -rf /var/www/html > /dev/null 2>&1; ${sudo} mv ./WoWonder /var/www/html; ${sudo} chmod -R 777 /var/www/html; cd /var/www/html; ${sudo} bash install.sh
}; fi
```