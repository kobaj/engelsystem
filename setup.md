# Setup instructions

These instructions will set up an instance of `Engelsystem-FC` as HTTP on port 8080
on an Amazon instance. They do not include changes that were made, post-installation,
to run it on port 443 as https.

## Create an EC2 instance (deprecated)

> :warning: Deprecated, please use the [fargate infrastructure](https://github.com/anthroarts/fargate-infrastructure) package to deploy to ec2 and setup the instance.

Go to AWS, then EC2, then Instances. Launch an instance with the following settings:

1. Give it any name.

2. Choose "Ubuntu Server 22.04 LTS (HVM), SSD Volume Type" as the Amazon Machine Image.

3. Architecture: 64-bit (x86)

4. Instance type: t2.micro (free tier eligible)

5. Get or create a key pair. Save the private and public keys!

6. Use a security group that allows TCP port 22 and 8080 traffic from the Internet.

7. Configure storage with 30 GiB gp2 root volume, not encrypted.

8. Launch the instance.

9. Keep track of the address of the server.

## Set up the instance (deprecated)

1. ssh into your server. The command will be something like `ssh -i ?????.pem ubuntu@ec2-??-???-???-???.compute-1.amazonaws.com`

2. In order to install docker, you need to update the software in your system. Here are the steps:

```
$ sudo apt-get update
$ sudo apt-get upgrade
```

Accept all defaults.

```
$ sudo apt-get install docker docker.io docker-compose
```

Accept all defaults.

```
$ sudo usermod -a -G docker ubuntu

$ sudo reboot
```

Log back into the system.

3. Check that you have docker permission by typing `docker ps`

4. Start up Engelsystem with the command `./startup.sh`. This will take some time to complete.

5. Once the startup is finished, you can visit the webpage at
http://ec2-??-???-???-???.compute-1.amazonaws.com:8080/login.

## Initialize the instance with types, shifts, and locations

1. The default user name is `admin` and the default password is `asdfasdf`.

2. Click on `admin` in the upper-right side, then choose `Settings`.

3. Under "Settings" is "Password". Click that.

4. Your old password remains `asdfasdf`. Enter a new password, then click "Save".

5. From here on out you can fill in the Gofur Types, Locations, and Shift Types. There are some helpful resources available in the wiki to accomplish this: https://wiki.furcon.org/doc/engelsystem-Npw9BF62KE#h-developer-instructions

6. Let everyone know that the service is up and ready!
