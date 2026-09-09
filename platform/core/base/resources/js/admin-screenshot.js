#!/usr/bin/env node

/**
 * Admin Panel Screenshot Capture Script
 *
 * Captures screenshots of admin panel pages for documentation.
 * Can be used with any Botble CMS project.
 *
 * Usage:
 *   Interactive mode (prompts for each page):
 *     npm run admin-screenshot -- --site=https://example.com
 *
 *   With config file:
 *     npm run admin-screenshot -- --site=https://example.com --config=screenshot-config.json
 *
 *   Single page:
 *     npm run admin-screenshot -- --site=https://example.com --path=/license-manager/products --name=products-list
 *
 * Options:
 *   --site          Base URL (required)
 *   --admin         Admin directory (default: admin)
 *   --output        Output directory (default: ./screenshots)
 *   --config        JSON config file with pages to capture
 *   --path          Single page path to capture (used with --name)
 *   --name          Output filename for single page (without .png)
 *   --username      Admin username for auto-login
 *   --password      Admin password for auto-login
 *   --wait          Wait time in ms before capture (default: 3000)
 *   --width         Viewport width (default: 1400)
 *   --height        Viewport height (default: 900)
 *   --headless      Run in headless mode (default: false)
 *   --help          Show this help message
 *
 * Config file format (JSON):
 *   {
 *     "pages": [
 *       { "name": "products-list", "path": "/license-manager/products", "description": "Products List" },
 *       { "name": "settings", "path": "/settings", "description": "Settings Page" }
 *     ],
 *     "portal": [
 *       { "name": "portal-login", "path": "/customer/login", "noAuth": true }
 *     ]
 *   }
 */

const puppeteer = require('puppeteer');
const path = require('path');
const fs = require('fs');
const readline = require('readline');

function prompt(question) {
    const rl = readline.createInterface({
        input: process.stdin,
        output: process.stdout,
    });
    return new Promise(resolve => {
        rl.question(question, answer => {
            rl.close();
            resolve(answer.trim());
        });
    });
}

function parseArgs() {
    const args = process.argv.slice(2);
    const options = {
        site: null,
        admin: 'admin',
        output: './screenshots',
        config: null,
        path: null,
        name: null,
        username: null,
        password: null,
        customerEmail: null,
        customerPassword: null,
        portal: false,
        wait: 3000,
        width: 1400,
        height: 900,
        headless: false,
        help: false,
    };

    const booleanFlags = ['help', 'portal', 'headless'];
    const numericFlags = ['wait', 'width', 'height'];

    for (let i = 0; i < args.length; i++) {
        const arg = args[i];
        if (arg.includes('=')) {
            const [key, ...valueParts] = arg.replace('--', '').split('=');
            const value = valueParts.join('=');

            if (booleanFlags.includes(key)) {
                options[key] = value === 'true' || value === '1' || value === '';
            } else if (numericFlags.includes(key)) {
                options[key] = parseInt(value, 10) || options[key];
            } else {
                options[key] = value;
            }
        } else if (arg.startsWith('--')) {
            const key = arg.slice(2);
            if (booleanFlags.includes(key)) {
                options[key] = true;
            }
        }
    }

    return options;
}

function showHelp() {
    console.log(`
Admin Panel Screenshot Capture Script

Captures screenshots of admin panel pages for documentation.

Usage:
  Interactive mode:
    npm run admin-screenshot -- --site=https://example.com

  With config file:
    npm run admin-screenshot -- --site=https://example.com --config=screenshot-config.json

  Single page:
    npm run admin-screenshot -- --site=https://example.com --path=/products --name=products-list

Options:
  --site=<url>       Base URL (required)
  --admin=<dir>      Admin directory (default: admin)
  --output=<dir>     Output directory (default: ./screenshots)
  --config=<file>    JSON config file with pages to capture
  --path=<path>      Single page path to capture
  --name=<name>      Output filename (without .png)
  --username=<str>   Admin username for auto-login
  --password=<str>   Admin password for auto-login
  --customerEmail=<str>    Customer email for portal auto-login
  --customerPassword=<str> Customer password for portal auto-login
  --portal           Also capture customer portal pages from config
  --wait=<ms>        Wait time before capture (default: 3000)
  --width=<px>       Viewport width (default: 1400)
  --height=<px>      Viewport height (default: 900)
  --headless         Run browser in headless mode
  --help             Show this help

Config file format:
  {
    "pages": [
      { "name": "products-list", "path": "/plugin/products", "description": "Products" }
    ],
    "portal": [
      { "name": "portal-login", "path": "/customer/login", "noAuth": true }
    ]
  }
`);
}

async function captureScreenshot(page, url, outputPath, options) {
    const { wait } = options;

    console.log(`  Loading: ${url}`);

    try {
        await page.goto(url, { waitUntil: 'networkidle2', timeout: 30000 });
        await new Promise(resolve => setTimeout(resolve, wait));

        // Hide toast notifications, alerts, and license warnings
        await page.evaluate(() => {
            // Hide toasts and notifications
            document.querySelectorAll('.toast, .notification, [class*="toast"]').forEach(el => {
                el.style.display = 'none';
            });
            // Hide license warning banners
            document.querySelectorAll('.alert-warning, .license-warning, [class*="license-notice"]').forEach(el => {
                el.style.display = 'none';
            });
            // Hide any alerts containing "license" text
            document.querySelectorAll('.alert').forEach(el => {
                if (el.textContent && el.textContent.toLowerCase().includes('license')) {
                    el.style.display = 'none';
                }
            });
        });

        await page.screenshot({ path: outputPath, fullPage: false });
        console.log(`  ✓ Saved: ${outputPath}`);
        return true;
    } catch (error) {
        console.log(`  ✗ Error: ${error.message}`);
        return false;
    }
}

async function autoLogin(page, options) {
    if (!options.username || !options.password) {
        return false;
    }

    console.log('Attempting auto-login...');
    try {
        await page.waitForSelector('input[name="username"], input[name="email"], #username, #email', { timeout: 5000 });

        const usernameField = await page.$('input[name="username"]') ||
                              await page.$('input[name="email"]') ||
                              await page.$('#username') ||
                              await page.$('#email');
        const passwordField = await page.$('input[name="password"]') || await page.$('#password');

        if (usernameField && passwordField) {
            await usernameField.type(options.username);
            await passwordField.type(options.password);

            const submitBtn = await page.$('button[type="submit"]') || await page.$('input[type="submit"]');
            if (submitBtn) {
                await submitBtn.click();
                await page.waitForNavigation({ waitUntil: 'networkidle2', timeout: 10000 });
                console.log('Login successful!');
                return true;
            }
        }
    } catch (e) {
        console.log('Auto-login failed: ' + e.message);
    }
    return false;
}

async function customerLogin(page, site, options) {
    if (!options.customerEmail || !options.customerPassword) {
        return false;
    }

    console.log('\nLogging into customer portal...');
    const loginUrl = `${site}/customer/login`;
    await page.goto(loginUrl, { waitUntil: 'networkidle2' });

    try {
        const emailField = await page.$('input[name="email"]') || await page.$('#email');
        const passwordField = await page.$('input[name="password"]') || await page.$('#password');

        if (emailField && passwordField) {
            await emailField.type(options.customerEmail);
            await passwordField.type(options.customerPassword);

            const submitBtn = await page.$('button[type="submit"]');
            if (submitBtn) {
                await submitBtn.click();
                await page.waitForNavigation({ waitUntil: 'networkidle2', timeout: 10000 });
                console.log('Customer login successful!');
                return true;
            }
        }
    } catch (e) {
        console.log('Customer auto-login failed: ' + e.message);
    }
    return false;
}

async function main() {
    const options = parseArgs();

    if (options.help) {
        showHelp();
        process.exit(0);
    }

    if (!options.site) {
        console.error('Error: --site parameter is required');
        console.error('Run with --help for usage information');
        process.exit(1);
    }

    // Remove trailing slash from site
    options.site = options.site.replace(/\/$/, '');

    // Load config file if provided
    let config = { pages: [], portal: [] };
    if (options.config) {
        const configPath = path.resolve(options.config);
        if (fs.existsSync(configPath)) {
            config = JSON.parse(fs.readFileSync(configPath, 'utf8'));
            console.log(`Loaded config from: ${configPath}`);
        } else {
            console.error(`Config file not found: ${configPath}`);
            process.exit(1);
        }
    }

    // Single page mode
    if (options.path && options.name) {
        config.pages = [{ name: options.name, path: options.path, description: options.name }];
    }

    console.log('');
    console.log('Admin Screenshot Capture');
    console.log('========================');
    console.log(`Site: ${options.site}`);
    console.log(`Admin: /${options.admin}`);
    console.log(`Output: ${options.output}`);
    console.log(`Pages to capture: ${config.pages.length}`);
    console.log('');

    // Ensure output directory exists
    const outputDir = path.resolve(options.output);
    if (!fs.existsSync(outputDir)) {
        fs.mkdirSync(outputDir, { recursive: true });
    }

    // Launch browser
    const browser = await puppeteer.launch({
        headless: options.headless ? 'new' : false,
        args: ['--no-sandbox', '--disable-setuid-sandbox', `--window-size=${options.width},${options.height}`],
        defaultViewport: { width: options.width, height: options.height },
    });

    const page = await browser.newPage();

    // Navigate to admin login page
    const adminLoginUrl = `${options.site}/${options.admin}`;
    console.log(`Opening admin panel: ${adminLoginUrl}`);
    await page.goto(adminLoginUrl, { waitUntil: 'networkidle2' });

    // Auto-login or wait for manual login
    const loggedIn = await autoLogin(page, options);
    if (!loggedIn && !options.headless) {
        console.log('');
        await prompt('Press Enter after you have logged in to continue...');
    }
    console.log('');

    // Capture admin pages
    if (config.pages.length > 0) {
        console.log('Capturing Admin Panel Screenshots:');
        console.log('-'.repeat(40));

        for (const pageInfo of config.pages) {
            const url = `${options.site}/${options.admin}${pageInfo.path}`;
            const outputPath = path.join(outputDir, `${pageInfo.name}.png`);
            console.log(`\n${pageInfo.description || pageInfo.name}:`);
            await captureScreenshot(page, url, outputPath, options);
        }
    }

    // Interactive mode - prompt for pages if no config
    if (config.pages.length === 0 && !options.headless) {
        console.log('Interactive mode - enter pages to capture');
        console.log('Enter path (e.g., /license-manager/products) or "done" to finish');
        console.log('');

        let continueCapturing = true;
        while (continueCapturing) {
            const pagePath = await prompt('Page path: ');
            if (!pagePath || pagePath.toLowerCase() === 'done' || pagePath.toLowerCase() === 'exit') {
                continueCapturing = false;
                continue;
            }

            const pageName = await prompt('Output filename (without .png): ');
            if (!pageName) continue;

            const url = `${options.site}/${options.admin}${pagePath}`;
            const outputPath = path.join(outputDir, `${pageName}.png`);
            console.log('');
            await captureScreenshot(page, url, outputPath, options);
            console.log('');
        }
    }

    // Capture customer portal pages
    if (options.portal && config.portal && config.portal.length > 0) {
        console.log('');
        console.log('Capturing Customer Portal Screenshots:');
        console.log('-'.repeat(40));

        // Capture public pages first
        for (const pageInfo of config.portal.filter(p => p.noAuth)) {
            const url = `${options.site}${pageInfo.path}`;
            const outputPath = path.join(outputDir, `${pageInfo.name}.png`);
            console.log(`\n${pageInfo.description || pageInfo.name}:`);
            await captureScreenshot(page, url, outputPath, options);
        }

        // Login for authenticated pages
        const authPages = config.portal.filter(p => !p.noAuth);
        if (authPages.length > 0) {
            const customerLoggedIn = await customerLogin(page, options.site, options);
            if (!customerLoggedIn && !options.headless) {
                console.log('\nPlease login to customer portal for authenticated pages...');
                await page.goto(`${options.site}/customer/login`, { waitUntil: 'networkidle2' });
                await prompt('Press Enter after you have logged in to customer portal...');
            }

            // Capture authenticated pages
            for (const pageInfo of authPages) {
                const url = `${options.site}${pageInfo.path}`;
                const outputPath = path.join(outputDir, `${pageInfo.name}.png`);
                console.log(`\n${pageInfo.description || pageInfo.name}:`);
                await captureScreenshot(page, url, outputPath, options);
            }
        }
    }

    console.log('');
    console.log('Screenshot capture complete!');
    console.log(`Images saved to: ${outputDir}`);

    await browser.close();
}

main().catch(console.error);
