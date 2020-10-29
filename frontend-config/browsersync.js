module.exports = {
    open: false,
    proxy:  {
        target:"http://drupal"
    },
    files: [
        'web/themes/custom/innovation_network/assets/dist/js/*',
        'web/themes/custom/innovation_network/assets/dist/css/*',
        'web/themes/custom/innovation_network/templates/**/*'
    ]
};
