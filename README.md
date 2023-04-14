# Spotify api discography search 

## Running the project 

- Clone this repo in your local machine 

- CD into the repo folder 

- Rename the file `.env.example` to `.env`

- Enter your Shopify credentials in the following fields found in the newly created `.env` file

  `SPOTIFY_CLIENT_ID=YOUR_CLIENT_KEY`

  `SPOTIFY_CLIENT_SECRET=YOUR_CLIENT_SECRET`

- You can obtain the credentials by creating an Spotify app [Shopify docs]( https://developer.spotify.com/documentation/web-api/tutorials/getting-started#create-an-app)

- Install php depedenciens by running the command `composer install` in your project's root folder

- Run the server in localhost by executing the followind command `php artisan serve --port=80` in your project's root folder
