FROM ubuntu:14.04
MAINTAINER Grant Hutchinson <h.g.utchinson@gmail.com>


RUN apt-get update && \
    apt-get install -y apache2 php5 php5-mysql php5-curl curl wget git && \
	cd /var/www/html/ && \
	git clone https://github.com/CheckoutCrypto/crypto-api  && \
	cp ./crypto-api/* . -r && cp ./crypto-api/.git . -r && cp ./crypto-api/.gitmodules . && \
	cp ./crypto-api/.gitignore . && git submodule init && git submodule update && \
	cp ./crypto-api/bootup.sh /root/bootup.sh && chmod +x /root/bootup.sh && \
	rm -r ./crypto-api  && rm ./bootup.sh && \
	cd /var/www/html/ && chmod 755 * -R && \
    apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*


EXPOSE 80
CMD ["/bin/bash", "/root/bootup.sh"]
