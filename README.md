# optimize-images

Modified version of https://github.com/vontainment/v-wordpress-image-optimize

This version is an highly modified version to adress a few issues with how the mu plugin works.

## Issues 
- It works very slow with larger images clients loved to upload 1mb + video's causing processing to take ages
- Wordpress looks up in the database what the to be used url instead using .webp by default

## Solutions

- Implement support for a cronjob via wp-cli and resize images every minute. This means there might be a slighth delay max 1 to 2 min but it is better then timeouts because processing takes to long
- Update on publish in post images to use webp

## Usage

- Upload Plugin
- Setup Cronjob to execute wp cli oi optimze

## Example on Hestia CP

As root:
```bash
v-add-user-wp-cli
```

If you have used the Quick Installer it has been allready installed!

In Hestia Panel:

```
cd /home/user/web/user.nl/public_html/ && /home/user/.wp-cli/wp oi optimize > /dev/null 2>&1
```
And execute it every minute. 
