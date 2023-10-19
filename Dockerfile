# 构建主镜像
FROM richarvey/nginx-php-fpm:2.1.2

# 复制项目文件
COPY . /var/www/science_tree
COPY ./.example.env /var/www/science_tree/.env
COPY ./deploy/nginx.conf /etc/nginx/sites-enabled/web.conf
COPY ./deploy/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf

COPY ./deploy/composer.phar /usr/bin/composer
RUN composer config -g repo.packagist composer https://mirrors.aliyun.com/composer/

# 安装扩展
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions
RUN install-php-extensions gd
RUN install-php-extensions redis
RUN install-php-extensions zip
RUN install-php-extensions pdo_mysql
RUN install-php-extensions bcmath
RUN install-php-extensions sockets

RUN sed -i '2,3d' /usr/local/etc/php/conf.d/docker-vars.ini && \
echo "post_max_size=60m" >> /usr/local/etc/php/conf.d/docker-vars.ini && \
echo "upload_max_filesize=30M" >> /usr/local/etc/php/conf.d/docker-vars.ini && \
echo "php_admin_value[error_reporting] = E_ALL & ~E_NOTICE & ~E_WARNING & ~E_STRICT & ~E_DEPRECATED" >> /usr/local/etc/php-fpm.conf

# 安装依赖
WORKDIR /var/www/science_tree
RUN chmod -R 777 runtime && chmod -R 777 public
RUN php -d memory_limit=-1 /usr/bin/composer install

EXPOSE 8899

