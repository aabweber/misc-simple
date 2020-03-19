// const CDP = require('chrome-remote-interface');
const URL = 'https://www.tiktok.com/';

const puppeteer = require('puppeteer');



(async function () {
    const browser = await puppeteer.launch();
    var page = await browser.newPage();
    // console.log(page);
    //*
    try {
        page._client.on('Network.dataReceived', event => {
            if(page._networkManager) {
                const request = page._networkManager._requestIdToRequest.get(event.requestId);
                console.log(request.url);
            }
            // if (!request.url.startsWith('data:')) {
            // const length = event.dataLength;
            // }
        });
    }catch(msg){
        console.log(msg);
    }
    await page.goto(URL);
    // await page.goto(URL, { waitUntil: 'networkidle0' });
     // */

    // const total = Object.values(resources).reduce((a, n) => a + n, 0);
    //
    // console.log(resources);
    // console.log(`TOTAL = ${total} (uncompressed)`);
})();