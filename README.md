# Apple Signin WordPress Plugin

When I wanted to integrate Apple Signin on my WordPress website, I was surpised that no such free plugin was available on internet (at least I was not able to find after search for good 3-4 hours). Hence I had to develop something of my own and now decided to release it as a proper plugin which anyone can install on their WordPress website in just a few minutes.

## Environment Variables

To run this project, you will need to add the following environment variables to **variable.php** file.

`client_id`

This is something like `com.example.com`

`post_login_url`

Where users should be redirected after successfully logging in

`kid`

This is Key ID and can be found on apple developer dashboard under Certificates, Identifiers & Profiles

`iss`

Also call Team ID, this can be found on apple developer dashboard under Certificates, Identifiers & Profiles as Identifiers

`private_key`

Your private key

## Installation

Once you have installed the plugin and changed the variables in **variables.php** file, you then need to add redirect url in you apple developer dashboard. URL should be like following:

```
https://www.yourwebsite.com/wp-admin/admin-ajax.php?action=apple_signin
```

## Technologies Used

**Server:** WordPress, PHP, Apple Developer

## 🚀 About Me

I'm a full stack developer with experties in WordPress, Laravel, AWS, React

## 🔗 Links

[![portfolio](https://img.shields.io/badge/my_portfolio-000?style=for-the-badge&logo=ko-fi&logoColor=white)](https://it.haq.life/)

[![linkedin](https://img.shields.io/badge/linkedin-0A66C2?style=for-the-badge&logo=linkedin&logoColor=white)](https://www.linkedin.com/in/abdulhaq0)

[![twitter](https://img.shields.io/badge/twitter-1DA1F2?style=for-the-badge&logo=twitter&logoColor=white)](https://twitter.com/AbdulHaqLife)
