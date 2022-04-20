include .env

build:
	docker build -t vk_nginx_image:latest -f nginx.Dockerfile .
	docker build -t vk_php_image:latest -f Dockerfile .