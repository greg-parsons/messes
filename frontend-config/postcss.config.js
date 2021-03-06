module.exports = {
    plugins: [
        require("autoprefixer"),
        require("postcss-pxtorem")( {
            "rootValue": "15",
            "replace": "false",
            "propWhiteList": [],
            "selectorBlack:ist": [
                ":before",
                ":after",
                "::before",
                "::after"
            ]
        }),
        require("cssnano")({
            "autoprefixer": "false"
        })
    ]
};
