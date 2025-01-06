module.exports = {
    apps: [
        {
            name: "schedule",
            script: "php artisan schedule:work",
        },
        {
            name: "queue",
            script: "php artisan queue:work",
        },
    ],
};
