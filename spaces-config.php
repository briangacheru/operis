<?php
// DigitalOcean Spaces Configuration
// DO NOT hardcode credentials here. Set these as environment variables or in a .env file
// that is excluded from version control via .gitignore.
//
// Required environment variables:
//   DO_SPACES_REGION      e.g. sfo3
//   DO_SPACES_ENDPOINT    e.g. https://sfo3.digitaloceanspaces.com
//   DO_SPACES_BUCKET      e.g. your-bucket-name
//   DO_SPACES_KEY         your Spaces access key
//   DO_SPACES_SECRET      your Spaces secret key

return [
    'region'   => getenv('DO_SPACES_REGION')   ?: '',
    'endpoint' => getenv('DO_SPACES_ENDPOINT') ?: '',
    'bucket'   => getenv('DO_SPACES_BUCKET')   ?: '',
    'access_key' => getenv('DO_SPACES_KEY')    ?: '',
    'secret_key' => getenv('DO_SPACES_SECRET') ?: '',
];
