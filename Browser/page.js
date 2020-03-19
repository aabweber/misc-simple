const puppeteer = require('puppeteer');

(async (url) => {
    console.log(url);
    const browser = await puppeteer.launch();
    const page = await browser.newPage();
    // await page.setRequestInterception(true);
    page.on('request', request => {
        console.log(request.url());
        // if (request.resourceType() === 'xhr') {
        //     console.log(request.url());
        // }
        // request.continue();
    });
    await page.goto(url);
    // await page.screenshot({path: 'example.png'});
    // await (100000);
    // sleep(1);
    await page.close();
    await browser.close();
})(process.argv[2]);
//// https://m.tiktok.com/share/item/list?secUid=MS4wLjABAAAAOuk7ln7hwp5OADS2OjDGI4H2kOzGmatMx6oEy-6BLj8_oekqP86PqSApc7PvS4ve&id=6744202558816912389&type=1&count=30&minCursor=0&maxCursor=0&shareUid=&lang=&_signature=La812gAgEBKaBxNl74oLBy2vPMAAHP8