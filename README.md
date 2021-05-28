# Muaath Bots
**Muaath Bots** is some PHP Telegram bots that can be recreated by everyone.

## Features
- Doesn't use any databases
- Open source
- Can hosted in Heroku free
- Configuration stored in environment variables
- All bots uses **Bot API 5.2** or later.

## Bots
**Avaliable bots:**  
- Test payment 2.0 bot | [@TestPaymentV2Bot](https://t.me/TestPaymentV2Bot).

**Coming soon:**
- getChat method bot | [@getChatMethodBot](https://t.me/getChatMethodBot).

## FAQ
| Question                                | Answer                                                                                                                   |
|-----------------------------------------|--------------------------------------------------------------------------------------------------------------------------|
| What's minimum version of bots?         | Bot API 5.2                                                                                                              |
| Why Muaath bots doesn't uses databases? | To make it free hosted in Heroku, Also because this project doesn't get money to buy good hosting offers good databases. |
| Is there any idea to pay for bots?      | Currently, No. Because this project hasn't any money source like some bots.                                              |

## Issues, Pull requests, And Questions
Please read [**Contributing guidelines**](CONTRIBUTING.md)

## Importing project
**Before importing this project, Read these instructions please:**
- Generate your tokens for the the imported Bots, And each token should follow (Bot settings) in `README.md` for each bot.
- Create Heroku account, At first we suggest using free Heroku.

**Import project now:**

[![Deploy](https://www.herokucdn.com/deploy/button.svg)](https://heroku.com/deploy?template=https://github.com/Muaath5/MuaathBots)

**(Optional) And if you want to edit on the code, Complete these instructions:**
- Install [Git](https://git-scm.com/book/en/v2/Getting-Started-Installing-Git) And [Heroku CLI](https://devcenter.heroku.com/articles/heroku-cli).
- Use command `cd <my_dir>` in terminal, And replace _<my_dir>_ with directory to create the project in.
- Use command `heroku git:clone -a <app_name>` in terminal, And replace _<app_name>_ with app name you choosed for Heroku.
- After editing the files, You can use `git push heroku main` to upload the code into Heroku.
- To upload the new code in GitHub, You'll need to process the following code (In Windows):
```sh
cd %USERPROFILE%
mkdir .ssh
cd .ssh
ssh-keygen -t ed25519 -C "same_email_registered_in_github@example.com" -f github_key
ssh-add github_key
```
- **Note:** Add your registered email address in GitHub in the email section.
- Now go to: [Add new SSH token in GitHub](https://github.com/settings/ssh/new)
- Open and copy `.pub` file and paste it in the field
- Add any title like: "*GitHub key*"
- Use `git remote add github ` <u>only first time</u> you want to upload code to GitHub.
- Use `git push github main` to upload (push) the **main** branch to GitHub.


## Licence
This project is under [GPL v3.0 Licence](LICENCE).