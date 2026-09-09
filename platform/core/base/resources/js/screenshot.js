#!/usr/bin/env node

/**
 * Screenshot Capture Script
 *
 * Captures screenshots of shortcode sections from a website to use as preview images.
 * This is a general-purpose tool that works with any theme.
 *
 * The script automatically finds shortcode sections using the data-shortcode-name attribute:
 *   <section data-shortcode-name="hero-banner">...</section>
 *
 * Usage:
 *   Interactive mode:
 *     npm run screenshot https://example.com
 *     npm run screenshot -- --site=https://example.com
 *
 *   Direct mode:
 *     npm run screenshot -- <shortcode-name> --site=https://example.com [options]
 *
 * Examples:
 *   npm run screenshot https://lara-mag.botble.com
 *   npm run screenshot -- hero-banner --site=http://localhost
 *   npm run screenshot -- contact-form --site=https://example.com --url=/contact
 *   npm run screenshot -- hero-banner --site=https://example.com --selector="banner-area"
 *
 * Options:
 *   --site      Base URL (default: http://localhost or from APP_URL env)
 *   --output    Output directory (default: platform/themes/{theme}/public/images/ui-blocks)
 *   --theme     Theme name (default: auto-detected from project)
 *   --url       Page URL path to capture from (default: /)
 *   --selector  CSS selector or class names (auto-converted to selector)
 *   --full      Capture full page instead of element
 *   --wait      Wait time in ms before capture (default: 10000)
 *   --width     Viewport width (default: 1200)
 *   --height    Viewport height (default: 800)
 *   --maxHeight Max screenshot height in px (default: 600, 0 for unlimited)
 *   --help      Show this help message
 *
 * Note: Use "--" after "npm run screenshot" to pass arguments to the script.
 * Note: Shortcode sections should have data-shortcode-name attribute for automatic detection.
 */

const puppeteer = require('puppeteer');
const path = require('path');
const fs = require('fs');
const readline = require('readline');

/**
 * Prompt user for input
 */
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

/**
 * Convert class list to valid CSS selector
 * Example: "inner-contact-area pt-80 pb-80" -> ".inner-contact-area.pt-80.pb-80"
 */
function toSelector(input) {
    if (!input) return input;

    // Already a valid selector (starts with ., #, [, or contains special chars)
    if (/^[.#\[]/.test(input) || /[>\+~\[\]=:]/.test(input)) {
        return input;
    }

    // Convert space-separated class names to CSS selector
    const classes = input.trim().split(/\s+/).filter(Boolean);
    if (classes.length > 0) {
        return '.' + classes.join('.');
    }

    return input;
}

/**
 * Detect theme from current working directory
 */
function detectTheme() {
    const themesDir = path.resolve('platform/themes');

    if (fs.existsSync(themesDir)) {
        const themes = fs.readdirSync(themesDir).filter(name => {
            const themePath = path.join(themesDir, name);
            return fs.statSync(themePath).isDirectory() && !name.startsWith('.');
        });

        // Return first theme found (alphabetically)
        if (themes.length > 0) {
            return themes.sort()[0];
        }
    }

    return 'ripple';
}

// Expand ~ to home directory
function expandHome(filepath) {
    if (!filepath) return filepath;
    if (filepath.startsWith('~/') || filepath === '~') {
        return filepath.replace('~', process.env.HOME || process.env.USERPROFILE);
    }
    return filepath;
}

/**
 * Check if a string is a URL
 */
function isUrl(str) {
    return /^https?:\/\//i.test(str);
}

// Parse CLI arguments (supports both --key=value and --key value)
function parseArgs() {
    const args = process.argv.slice(2);
    const options = {
        shortcode: null,
        site: process.env.APP_URL || 'http://localhost',
        theme: null, // Will be auto-detected if not specified
        output: null,
        url: null,
        selector: null,
        full: false,
        wait: 10000,
        width: 1200,
        height: 800,
        maxHeight: 600,
        help: false,
    };

    const booleanFlags = ['full', 'help'];
    const numericFlags = ['wait', 'width', 'height', 'maxHeight'];
    let i = 0;

    while (i < args.length) {
        const arg = args[i];

        if (arg.startsWith('--')) {
            const hasEquals = arg.includes('=');

            if (hasEquals) {
                // Format: --key=value
                const [key, ...valueParts] = arg.slice(2).split('=');
                const value = valueParts.join('='); // Handle values with = in them

                if (booleanFlags.includes(key)) {
                    options[key] = true;
                } else if (numericFlags.includes(key)) {
                    options[key] = parseInt(value, 10) || options[key];
                } else {
                    options[key] = expandHome(value);
                }
            } else {
                // Format: --key value or --flag
                const key = arg.slice(2);

                if (booleanFlags.includes(key)) {
                    options[key] = true;
                } else if (i + 1 < args.length && !args[i + 1].startsWith('--')) {
                    // Next arg is the value
                    i++;
                    const value = args[i];
                    if (numericFlags.includes(key)) {
                        options[key] = parseInt(value, 10) || options[key];
                    } else {
                        options[key] = expandHome(value);
                    }
                }
            }
        } else if (isUrl(arg)) {
            // URL provided as first argument - use as site
            options.site = arg;
        } else if (!options.shortcode) {
            options.shortcode = arg;
        }

        i++;
    }

    // Auto-detect theme if not specified
    if (!options.theme) {
        options.theme = detectTheme();
    }

    // Set default output directory
    if (!options.output) {
        options.output = `platform/themes/${options.theme}/public/images/ui-blocks`;
    }

    return options;
}

function showHelp() {
    console.log(`
Screenshot Capture Script

Captures screenshots of shortcode sections from a website to use as preview images.
This is a general-purpose tool that works with any theme.

The script automatically finds shortcode sections using the data-shortcode-name attribute:
  <section data-shortcode-name="hero-banner">...</section>

It waits for the shortcode to have .shortcode-lazy-loading-loaded class before capturing.

Usage:
  Interactive mode (prompts for shortcode name, selector, URL):
    npm run screenshot https://example.com
    npm run screenshot -- --site=https://example.com

  Direct mode (single capture):
    npm run screenshot -- <shortcode-name> --site=https://example.com [options]

Examples:
  npm run screenshot https://lara-mag.botble.com
  npm run screenshot -- hero-banner --site=http://localhost
  npm run screenshot -- contact-form --site=https://example.com --url=/contact
  npm run screenshot -- hero-banner --site=https://example.com --selector="banner-area-two"
  npm run screenshot -- coming-soon --site=http://localhost --url=/coming-soon --full

Options:
  --site=<url>       Base URL (default: http://localhost or APP_URL env)
  --theme=<name>     Theme name (default: auto-detected from project)
  --output=<dir>     Output directory (default: platform/themes/{theme}/public/images/ui-blocks)
  --url=<path>       Page URL path to capture from (default: /)
  --selector=<css>   CSS selector or class names (auto-converted to selector)
                     Example: "inner-contact-area pt-80" -> ".inner-contact-area.pt-80"
  --full             Capture full page instead of element
  --wait=<ms>        Wait time before capture (default: 10000)
  --width=<px>       Viewport width (default: 1200)
  --height=<px>      Viewport height (default: 800)
  --maxHeight=<px>   Max screenshot height (default: 600, 0 for unlimited)
  --help             Show this help

Note: Use "--" after "npm run screenshot" to pass arguments to the script.

Your shortcode Blade templates should wrap content with data-shortcode-name:
  <section data-shortcode-name="{{ $shortcode->getName() }}">
    ...
  </section>
`);
}


async function captureScreenshot(options) {
    const { shortcode, site, output, wait, full, width, height, maxHeight } = options;

    const url = options.url || '/';
    const fullPage = full || false;

    // Build selector: use custom selector (converted to valid CSS) or data-shortcode-name attribute
    const selector = options.selector
        ? toSelector(options.selector)
        : `[data-shortcode-name="${shortcode}"]`;

    const fullUrl = `${site}${url}`;
    const outputPath = path.resolve(output, `${shortcode}.png`);

    console.log('');
    console.log('='.repeat(50));
    console.log(`Capturing: ${shortcode}`);
    console.log('='.repeat(50));
    console.log(`URL: ${fullUrl}`);
    console.log(`Selector: ${selector}`);
    console.log(`Viewport: ${width}x${height}`);
    console.log(`Max height: ${maxHeight > 0 ? maxHeight + 'px' : 'unlimited'}`);
    console.log(`Output: ${outputPath}`);
    console.log('');

    // Ensure output directory exists
    const outputDir = path.dirname(outputPath);
    if (!fs.existsSync(outputDir)) {
        fs.mkdirSync(outputDir, { recursive: true });
    }

    // Launch browser
    const browser = await puppeteer.launch({
        headless: 'new',
        args: ['--no-sandbox', '--disable-setuid-sandbox'],
    });

    try {
        const page = await browser.newPage();
        await page.setViewport({ width, height, deviceScaleFactor: 1 });

        console.log('Loading page...');
        await page.goto(fullUrl, { waitUntil: 'networkidle2', timeout: 30000 });

        // Wait for initial content
        await new Promise(resolve => setTimeout(resolve, wait));

        // Wait for shortcode to be fully loaded (has .shortcode-lazy-loading-loaded class)
        const loadedSelector = `${selector}.shortcode-lazy-loading-loaded, ${selector}:not(.shortcode-lazy-loading)`;
        console.log('Waiting for shortcode to load...');

        try {
            await page.waitForSelector(loadedSelector, { timeout: 10000 });
            console.log('Shortcode loaded.');
        } catch (e) {
            console.log('Note: Shortcode may not use lazy loading, proceeding...');
        }

        // Additional wait for animations/content to settle
        await new Promise(resolve => setTimeout(resolve, 500));

        // Hide headers, navigation, and other fixed/sticky overlays
        await page.evaluate(() => {
            const selectorsToHide = [
                // Cookie notices
                '[class*="cookie"]',
                '[class*="consent"]',
                '.site-notice',
                // Fixed/sticky elements
                '.fixed-top',
                '.sticky-top',
                '[style*="position: fixed"]',
                '[style*="position: sticky"]',
                // Common header selectors
                'header',
                '.header',
                '.site-header',
                '.main-header',
                '.header-area',
                '.navbar-fixed',
                '.sticky-header',
                '.sticky-menu',
                '#header',
                'nav.navbar',
                // Top bars
                '.top-bar',
                '.topbar',
                '.header-top',
            ];

            selectorsToHide.forEach(selector => {
                document.querySelectorAll(selector).forEach(el => {
                    el.style.setProperty('display', 'none', 'important');
                    el.style.setProperty('visibility', 'hidden', 'important');
                    el.style.setProperty('position', 'absolute', 'important');
                    el.style.setProperty('top', '-9999px', 'important');
                });
            });

            // Also hide any element with position:fixed or position:sticky via computed style
            document.querySelectorAll('*').forEach(el => {
                const style = window.getComputedStyle(el);
                if (style.position === 'fixed' || style.position === 'sticky') {
                    el.style.setProperty('display', 'none', 'important');
                }
            });

            // Remove PageSpeed optimization elements
            document.querySelectorAll('[class*="page_speed_"]').forEach(el => {
                el.remove();
            });
        });

        // Wait for page to reflow after hiding elements
        await new Promise(resolve => setTimeout(resolve, 300));

        // Find element
        let element = await page.$(selector);

        if (!element) {
            console.error(`\n✗ Element not found: ${selector}`);
            console.error('Make sure the shortcode section has data-shortcode-name attribute or use --selector option.');
            return false;
        }

        console.log(`Found element: ${selector}`);

        if (!fullPage) {
            // Scroll element into view to ensure it's fully rendered
            await element.evaluate(el => el.scrollIntoView({ behavior: 'instant', block: 'center' }));
            await new Promise(resolve => setTimeout(resolve, 500));

            // Get element bounding box for maxHeight clipping
            const boundingBox = await element.boundingBox();
            const screenshotOptions = { path: outputPath };

            // Apply maxHeight clipping if element is taller than maxHeight
            if (maxHeight > 0 && boundingBox && boundingBox.height > maxHeight) {
                screenshotOptions.clip = {
                    x: boundingBox.x,
                    y: boundingBox.y,
                    width: boundingBox.width,
                    height: maxHeight,
                };
                console.log(`Clipping height from ${Math.round(boundingBox.height)}px to ${maxHeight}px`);
            }

            // Take screenshot of just the element
            await element.screenshot(screenshotOptions);
            console.log(`\n✓ Screenshot saved: ${outputPath}`);
            return true;
        }

        // Full page screenshot
        await page.screenshot({
            path: outputPath,
            fullPage: true,
        });
        console.log(`\n✓ Screenshot saved (full page): ${outputPath}`);
        return true;

    } catch (error) {
        console.error(`\n✗ Error: ${error.message}`);
        return false;
    } finally {
        await browser.close();
    }
}

async function main() {
    const options = parseArgs();

    if (options.help) {
        showHelp();
        process.exit(0);
    }

    console.log(`Theme: ${options.theme}`);
    console.log(`Site: ${options.site}`);
    console.log('');

    // Interactive mode: loop to capture multiple shortcodes
    let continueCapturing = true;

    while (continueCapturing) {
        let shortcode = options.shortcode;
        let selector = options.selector;

        // Prompt for shortcode name if not provided
        if (!shortcode) {
            shortcode = await prompt('Shortcode name (or "exit" to quit): ');
            if (!shortcode || shortcode.toLowerCase() === 'exit') {
                console.log('Bye!');
                break;
            }
        }

        // Prompt for selector (optional)
        if (!selector && !options.shortcode) {
            selector = await prompt('Selector (press Enter to use data-shortcode-name): ');
        }

        // Prompt for URL if not provided and in interactive mode
        let url = options.url;
        if (!url && !options.shortcode) {
            url = await prompt('URL path (press Enter for /): ');
            url = url || '/';
            // Ensure URL starts with /
            if (url && !url.startsWith('/')) {
                url = '/' + url;
            }
        }

        const captureOptions = {
            ...options,
            shortcode,
            selector: selector || null,
            url: url || options.url,
        };

        await captureScreenshot(captureOptions);

        // If shortcode was provided via CLI, exit after one capture
        if (options.shortcode) {
            continueCapturing = false;
        } else {
            console.log('');
        }
    }

    process.exit(0);
}

main();
