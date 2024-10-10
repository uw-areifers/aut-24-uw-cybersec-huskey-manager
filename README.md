# Week 3 | Cryptography
Cryptography plays a crucial role in cybersecurity by safeguarding the confidentiality, integrity, and availability of information, aligning with the principles of the CIA triad. Through the use of cryptographic techniques, sensitive data can be encrypted, rendering it unreadable to unauthorized individuals and ensuring confidentiality. The utilization of encryption also contributes to the availability of services by preventing disruptions and unauthorized access. By incorporating cryptographic measures, cybersecurity strategies can establish a robust defense against a range of threats, providing a foundation for secure communication, data storage, and overall information protection.

## Part 1: Utilizing HTTPS
As we have seen in the last lab, leaving connections to our web application unencrypted can leave us vulnerable to main-in-the-middle attacks. In order to ensure that all connections to our site are properly encrypted, we can utilize Secure Sockets Layer (SSL) protocol, which will utilize cryptography for secure communication over a network. HTTPS is a protocol for HTTP communication over SSL, which allows people to securely access our site. To do this, we need to generate some certificates for our web application. We can use OpenSSL to generate our public certificate and our key.

In order to generate our certificates, we are going to use a program called OpenSSL. OpenSSL is a tool-kit for general purpose cryptography and secure network communication. It can also generate SSL certificates for us to use!

### 1. Installing OpenSSL (Windows only)
If you are using a Mac, OpenSSL already come built in with your OS. If you are using Windows, you will need to install WSL (Windows Subsystem for Linux), which will allow you to run a Linux environment directly on your PC without needing a VM or dual booting. The Ubuntu distro of WSL comes with OpenSSL pre-installed, making it perfect for our needs.

1. Open your terminal and enter the following command.
    ```
    wsl --install -d Ubuntu
    ```

2. During the install, you will be prompted to create you UNIX username and password. This can be anything, so pick something you will remember.

3. Once installed you should get an output similar to the one shown below:

    ![WSL output](/lab-writeup-imgs/wsl_install_output.png)

4. Next, we will update all of our packages and install any upgrades available. In the WSL terminal, enter the following command:

    ```
    sudo apt update && sudo apt upgrade -y
    ```

5. Now, we can cd into our C: drive by entering the following command, updating the `<your_user_account>` with the user account on your PC:

    ```
    cd /mnt/c/Users/<your_user_account>
    ```

6. You should now be in your user directory in the WSL terminal, from here we can continue with the following steps to generate our certs!

### 2. Generating our Certificates

Now that we have OpenSSL configured, let's start generating our certs! We will first need to generate a certificate signing request (CSR). A CSR is a request that will be sent to a certificate authority (CA) to obtain a signed certificate. The signed certificate is a way to authenticate the identity of our site, so that users who visit it can know that our website was verified by a trusted third-party (CA) and that it is not a malicious website.

Our certificate will contain our websites public key, which clients can use to encrypt data sent to our website. We can then use our private key to decrypt the data that was encrypted using our public key.

1. Open your terminal and cd into the 'certs' folder in your project directory. This is where our certificate and key will live.

3. In the terminal, enter the following command to generate your certificate and key:

    ```
    openssl req -newkey rsa:2048 -nodes -keyout localhost.key -subj "/C=US/ST=WA/L=SEA/O=UW/CN=localhost/OU=iSchool" -out localhost.csr
    ```
    - Let's understand what each part of this command means:

        - `openssl req`: This command is to indicate we want to generate a certificate signing request (CSR).
        - `-newkey rsa:2048`: This option specifies that a new RSA private key should be generated. The key will have a length of 2048 bits.
        - `-nodes`: This flag is used to indicate that the key should not be encrypted with a passphrase. We will use this flag so that we don't have to enter a password every time we spin up our docker container.
        - `-keyout localhost.key`: This specifies that the filename where the generated private key should be saved. In this case, 'localhost.key'.
        - `-subj "/C=US/ST=WA/L=SEA/O=UW/CN=localhost/OU=iSchool"`: This portion sets the subject, which is the information about the entity the certificate is for. This is important for the certificate signing request, so that the issuer signing the cert knows who it belongs to.
            - `C=US`: Country = United States
            - `ST=WA`: State = Washington
            - `L=SEA`: Locality = Seattle
            - `O=UW`: Organization = University of Washington
            - `CN=localhost`: Common Name = localhost (the domain the certificate is for)
            - `OU=iSchool`: Organizational Unit = iSchool
        - `-out localhost.csr`: Finally, this flag specifies the name of the file where our certificate signing request will be saved, in this case 'localhost.csr'

4. Now that we have our certificate signing request saved to `localhost.csr`, we need our certificate authority to provide us a signed cert. We have created a CA, `iSchool-RootCA`, who's certificate and key is saved in the `./certs` directory.

    Typically, you wouldn't have the key file of the CA, and when you submit you CSR, the CA would connect to and verify your website is actually hosted on your domain. Since this is all hosted locally within docker, you do not have your own domain to associate with your website. This is why we provided you with our CA's key, so that it can run from your local machine and provide you with a certificate for `localhost`.

    To have our CA provide you with a signed certificate, run the following command:

    ```
    openssl x509 -req -extfile <(printf "subjectAltName=DNS:localhost") -days 365 -in localhost.csr -CA iSchool-RootCA.crt -CAkey iSchool-RootCA.key -CAcreateserial -out localhost.crt
    ```
    - Below is a breakdown of each part of this command:
        - `openssl x509`: This tells openssl that we are intending to generate a certificate in x509 format.
        - `-req`: This portion specifies the input file as a Certificate Signing Request, or .csr.
        - `-extfile <(printf "subjectAltName=DNS:localhost")`: This option includes a subject alternative name (SAN) extension for the certificate. RFC 2818 published in May of 2000 deprecates the use of Common Name for HTTPS certificates and instead uses the Subject Alternative Name extension for name verification.
        - `-days 365`: This option sets the validity period of the certificate to 365 days.
        - `-in localhost.csr`: This specifies the input file of the certificate signing request, in this case 'localhost.csr'
        - `-CA iSchool-RootCA.crt`: This specifies the filename of the Certificate Authority's Root Certificate that will be used to sign our certificate.
        - `-CAkey iSchool-RootCA.key`: This specifies the filename of the private key associated with the Root CA certificate. The private key is needed to sign our certificate.
        - `-CAcreateserial`: This option instructs OpenSSL to create a serial number file the CA. The serial number is used to uniquely identify each certificate issued by the CA.
        - `-out localhost.crt`: This specifies the output file where the generated certificate should be saved. In this case, 'localhost.crt'

5. In the `/certs/` directory, you should now see two files:
    - `localhost.crt`
    - `localhost.key`

    Congratulations! You have successfully generated your own certificate and key! Now, we will configure nginx to use our newly generated certs :)

### 3. Configuring nginx for HTTPS

Now that we have our certificate, let's start configuring our nginx server to use HTTPS!

1. First, we will need to tell nginx where our certificate and key are located:
    - In `docker-compose.yaml` add the following lines under services > server > volumes:
        - `./certs/localhost.crt:/etc/nginx/ssl/localhost.crt`
        - `./certs/localhost.key:/etc/nginx/ssl/localhost.key`
    - This will map our cert/key file to the directory `/etc/nginx/ssl/` in our nginx container.

2. Next, edit your `docker-compose.yaml`file to instruct our nginx server to listen on port 443 instead of port 80.
    - Under services > server > ports, change the port from `"80:80"` to `"443:443"`.
    - We specify a range of ports that our docker container will listen on, and in this case we only want to listen on 443.

3. Now that we have our certificate in place as well as port 443 open, we can edit our `nginx-default.conf` file to use ssl on port 443.
    - On line 2, we see that our server is listening on port 80, change this port to 443.
    - Nginx will default to HTTP regardless of the port we specify. In order to correct this, we need to add `ssl` after we specify the port. Line 2 of `nginx-default.conf` file should look like this:
        - `listen 443 ssl;`
    - We also need to specify in this file where our certificate and key are located in our nginx container. After we specify our server_name, add the following lines:

        - `ssl_certificate /etc/nginx/ssl/localhost.crt;`
        - `ssl_certificate_key /etc/nginx/ssl/localhost.key;`

4. You should now be able to access your web app on port 443 over HTTPS! To verify this, redeploy your docker image and try to access [https://localhost:443](https://localhost:443).

    You will see a warning similar to the one below when you first try to access your website:

    ![Warning](/lab-writeup-imgs/warning.png)

    Although we have provided our client with a certificate, the client doesn't know our Certificate Authority, making them untrusted by our computer/browser. Utilizing certificates requires that we have a trusted third-party, otherwise anyone can make a certificate (as you have just seen) making them pretty meaningless. 

    If you click, 'Advanced...' you should be able to ignore the warning and continue to the site. Although untrusted, our site is now utilizing cryptography to protect data in transit! 
    
    When we proceed to the website you should see a warning similar to the one below:

    ![Invalid certificate](/lab-writeup-imgs/cert_not_valid.png)

    Note that our certificate is not valid. This is because the iSchool-RootCA is not trusted by our computer and browser. In lecture, we will learn more about the importance of a trusted third party and how we can allow our machine to trust the INFO310 certificate authority!
    
    In part two we will be putting this to the test by performing our MiTM attack again!

## Part 2: Testing our cryptography:

Now that we have successfully utilized encryption, when performing MiTM attacks we should no longer be able to view the sensitive data of our victim.

(Note: You will still be able to _see_ the network traffic, however you will not be able to see any of the sensitive data in the network packets. This relates to the idea of confidentiality != secrecy)

For this section, we will redo our MiTM attack and see that we are unable to see sensitive data. Make sure you take a screenshot of the encrypted network traffic. For steps on how to complete the MiTM attack, refer to the `README.md` in the `networking` branch of this repository. 

Below is an example of the encrypted network traffic you should now be able to see:

![Encrypted Traffic](/lab-writeup-imgs/encrypted_traffic.png)

While we can see our web application performing the key exchange with our browser, we cannot view the raw HTTP traffic anymore!

