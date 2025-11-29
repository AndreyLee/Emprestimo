
import asyncio
from playwright.async_api import async_playwright

async def main():
    async with async_playwright() as p:
        browser = await p.chromium.launch(headless=True)
        page = await browser.new_page()
        try:
            # Go to the login page
            await page.goto("http://localhost:89/login.php", timeout=10000)

            # Fill in the credentials with the correct selector
            await page.fill('input[name="nome"]', 'admin')
            await page.fill('input[name="senha"]', 'password')

            # Click the login button and wait for navigation
            async with page.expect_navigation():
                await page.click('button[type="submit"]')

            # After login, we should be on index.php. Take a screenshot.
            await page.screenshot(path="/home/jules/verification/error_page_screenshot.png")
            print("Screenshot taken after login attempt.")

        except Exception as e:
            print(f"An error occurred: {e}")
            # Take a screenshot even if an error occurs to see the state
            await page.screenshot(path="/home/jules/verification/error_page_screenshot.png")
        finally:
            await browser.close()

if __name__ == "__main__":
    asyncio.run(main())
