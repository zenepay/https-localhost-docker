
# Dev systems with docker for with ssl for local host

## Installation
```sh
git clone https://github.com/zenepay/https-localhost-docker.git sail_share
cd sail_share

```


This will make 3 docker images for:
1. nginx to route url eg project1.dev.localhost where project1 is running ohter ports eg 8801
2. mariadb database port 3306
3. phpmyadmin to mange mariadb port: 8890

### Setup SSL on localhost
## Step 1: Generate a CA Certificate
~~~sh
mkdir nginx-certs
cd nginx-certs
openssl genrsa -out ca.key -des3 2048
openssl req -x509 -sha256 -new -nodes -days 3650 -key ca.key -out ca.pem

~~~

## Step 2: Generate Certificate, Signed By Our CA
We already has resources/localhost.ext file, if not yet exist here please using this:
~~~conf
authorityKeyIdentifier=keyid,issuer
basicConstraints=CA:FALSE
keyUsage = digitalSignature, nonRepudiation, keyEncipherment, dataEncipherment
extendedKeyUsage = serverAuth, clientAuth
subjectAltName = @alt_names
[req]
req_extensions = req_ext
[req_distinguished_name]
commonName_default = localhost
[req_ext]
subjectAltName = @alt_names
[alt_names]
DNS.1 = *.dev.localhost
DNS.2 = dev.localhost
DNS.3 = localhost
~~~

Now generate locahost.key file with this command
### Generate a private key
Choose a simple passphrase eg. NginXpass for your key. Enter it, re-enter it.
You are still in the resources folder
~~~sh
openssl genrsa -out localhost.key -des3 2048
~~~
 -Generate certificate signing request using key.
 -Enter the passphrase eg. NginXpass that you chose for the key -Choose defaults or enter information as appropriate.
 Don't worry about entering anything for "challenge password"
~~~sh
openssl req -new -key localhost.key -out localhost.csr
~~~
### Use the passphrase that you chose for the CA KEY in Step 1.
~~~sh
openssl x509 -req -in localhost.csr -CA ca.pem -CAkey ca.key \
-CAcreateserial -days 3650 -sha256 \
-extfile ../resources/localhost.ext -out localhost.crt
~~~

 Use the passphrase eg. NginXpass chosen for the localhost key,
 which is NOT the same as the CA key.
~~~sh
openssl rsa -in localhost.key -out localhost.decrypted.key
 ~~~
## Step 3: Import CA Certificate to Browsers
in brownser chrome put url as:
chrome://settings/certificates
Click Import then browse to nginx-certs/ca.pem
Click on the box that says “Trust this certificate for identifying websites.” Click on “OK.”

## Step 4: set ip for host in hosts file
C:\Windows\System32\drivers\etc\hosts
eg:

```
127.0.0.1 kubernetes.docker.internal localhost mariadb dev.localhost *.dev.localhost

192.168.1.111 host.docker.internal
192.168.1.111 gateway.docker.internal
```
## Step 5: edit global.pass
Change your file /nginx/keys/global.pass eg. NginXpass by enter your passphase of the key created above

## Step 6: edit default.conf.template
Change server_name to the subdomain, you want
Chage port to where the docker image is run eg port 8000
proxy_pass	http://host.docker.internal:8000;
```
server {
    listen  443 ssl;
    server_name subdomain.dev.localhost;

    # Self signed certificates
    # Don't use them in a production server!
    ssl_certificate     /etc/nginx/certs/localhost.crt;
    ssl_certificate_key /etc/nginx/certs/localhost.key;

    location / {
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header Host $host;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_pass	http://host.docker.internal:8000;
    }
}
```
## Step 7: set network of your docker project to the same
Edit your docker-compose.yml on your docker project (not this project)
must have network zen-network and external: true as below
```
networks:
    sail:
        driver: bridge
        name: zen-network
        external: true
```
## Step 8: create with docker compose
create .env file
```sh
cd ..
mv .env.example .env
mkdir mariadb
```
Now open Docker Desktop to let it start the service,
after that run following to build the image
~~~sh
docker compose up
~~~