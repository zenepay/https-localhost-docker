
# Dev systems with docker for with ssl for local host
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
cd ..
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
127.0.0.1 kubernetes.docker.internal localhost mysql dev.localhost *.dev.localhost

192.168.1.111 host.docker.internal
192.168.1.111 gateway.docker.internal
```
## Step 5: edit global.pass
Change your file /nginx/keys/global.pass eg. NginXpass by enter your passphase of the key created above

## Step 5: create with docker compose
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