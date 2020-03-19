'use strict';

const puppeteer = require('puppeteer');

(async(url) => {
    const browser = await puppeteer.launch({
        "args": [
            "--remote-debugging-port=9222",
            "--window-size=1920,1080",
            "--mute-audio",
            "--disable-notifications",
            "--force-device-scale-factor=0.8",
            "--no-sandbox",
            "--disable-setuid-sandbox"
        ],
        "defaultViewport": {
            "height": 1080,
            "width": 1920
        },
        "headless": true
    });
    const page = await browser.newPage();
    await page.setUserAgent('Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 Safari/537.36');

    // await page.setRequestInterception(true);
    /*
    page.on('response', res => {
        var req = res.request();
        var reqUrl = req.url();
        if (reqUrl.startsWith('data:')) {
            return
        }

        if (req.resourceType() === 'xhr') {
            res.buffer().then(
                b => {
                    console.log(reqUrl);
                    console.log(`${b}`);
                },
                e => {
                    console.error(`failed: ${e}`);
                }
            );
        }
    });
     */
    // page.on('requestfinished', request => {
    //     console.log(request.resourceType()+' ----------- '+request.url());
    //     if (request.resourceType() === 'xhr'){
            // console.log(request.url());
            // console.log(1);
            // let buffer = await request.response().buffer();
            // console.log();
            // console.log(2);
        // }
    // });
    await page.goto(url, {waitUntil: 'networkidle0'});
    // await page.screenshot({path: 'news.png', fullPage: true});
    const element = await page.$("input[name=\"token\"]");
    const text = await page.evaluate(element => element.value, element);

    await browser.close();
})(process.argv[2]);