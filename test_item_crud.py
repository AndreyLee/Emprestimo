
import os
import time
import re
from playwright.sync_api import sync_playwright, expect

def run_test():
    with sync_playwright() as p:
        browser = p.chromium.launch(headless=True)
        context = browser.new_context(
            viewport={'width': 1280, 'height': 720},
            ignore_https_errors=True
        )
        page = context.new_page()

        try:
            base_url = "http://localhost:89"
            print(f"Navigating to {base_url}/login.php")
            page.goto(f"{base_url}/login.php")

            # Login
            print("Logging in as admin...")
            page.fill('input[name="nome"]', 'admin')
            page.fill('input[name="senha"]', 'password')
            page.click('button[type="submit"]')
            print("Login submitted.")

            page.wait_for_url(f"{base_url}/index.php")
            print("Successfully logged in, redirected to index.php.")

            # Go to Gerenciar Itens page
            print("Navigating to Gerenciar Itens page...")
            page.locator('a:has-text("Gerenciar Itens")').click()
            page.wait_for_url(f"{base_url}/gerenciar_itens.php")
            print("On Gerenciar Itens page.")

            # --- Test Create ---
            print("--- Testing Create ---")
            item_name = "Projetor Epson X49"
            item_qty = "10"
            print(f"Adding new item: {item_name}")
            page.click('button:has-text("Adicionar Novo Item")')

            modal = page.locator('#addItemModal')
            expect(modal).to_be_visible()

            modal.locator('#nome').fill(item_name)
            modal.locator('#quantidade_total').fill(item_qty)
            modal.locator('button[type="submit"]:has-text("Salvar")').click()

            # Corrected: Wait for the standard SweetAlert modal, not a toast
            success_modal = page.locator('.swal2-popup.swal2-modal.swal2-icon-success.swal2-show')
            expect(success_modal).to_be_visible()
            expect(success_modal.locator('#swal2-title')).to_have_text("Item cadastrado com sucesso!")

            # Verify item in table after the alert is gone
            print("Verifying item in the table...")
            row = page.locator(f'tr:has-text("{item_name}")')
            expect(row).to_be_visible()
            expect(row.locator('td').nth(2)).to_have_text(item_qty)
            expect(row.locator('td').nth(3)).to_have_text(item_qty)
            print("Item created successfully.")

            # --- Test Edit ---
            print("--- Testing Edit ---")
            new_item_name = "Projetor BenQ WXGA"
            new_item_qty = "15"
            print(f"Editing item to: {new_item_name}")

            row.locator('a.btn-warning:has-text("Editar")').click()
            page.wait_for_url(re.compile(r'.*editar_item\.php\?id=\d+'))
            print("On Edit Item page.")

            page.fill('#nome', new_item_name)
            page.fill('#quantidade_total', new_item_qty)
            page.fill('#quantidade_disponivel', "12")
            page.click('button[type="submit"]:has-text("Salvar Alterações")')

            page.wait_for_url(f"{base_url}/gerenciar_itens.php")
            print("Redirected back to Gerenciar Itens page.")

            # Corrected: Wait for the standard SweetAlert modal
            edit_success_modal = page.locator('.swal2-popup.swal2-modal.swal2-icon-success.swal2-show')
            expect(edit_success_modal).to_be_visible()
            expect(edit_success_modal.locator('#swal2-title')).to_have_text("Item atualizado com sucesso!")

            # Verify changes in table
            print("Verifying updated item in the table...")
            expect(page.locator(f'tr:has-text("{item_name}")')).not_to_be_visible()

            new_row = page.locator(f'tr:has-text("{new_item_name}")')
            expect(new_row).to_be_visible()
            expect(new_row.locator('td').nth(2)).to_have_text(new_item_qty)
            expect(new_row.locator('td').nth(3)).to_have_text("12")
            print("Item edited successfully.")

            # --- Test Delete ---
            print("--- Testing Delete ---")
            print(f"Deleting item: {new_item_name}")

            new_row.locator('button.excluir-btn').click()

            confirm_modal = page.locator('.swal2-popup.swal2-modal.swal2-icon-warning.swal2-show')
            expect(confirm_modal).to_be_visible()
            print("SweetAlert confirmation dialog appeared.")

            confirm_modal.locator('button.swal2-confirm').click()
            print("Clicked confirm delete button.")

            # Corrected: Wait for the standard SweetAlert modal for delete success
            delete_success_modal = page.locator('.swal2-popup.swal2-modal.swal2-icon-success.swal2-show')
            expect(delete_success_modal).to_be_visible()
            expect(delete_success_modal.locator('#swal2-title')).to_have_text("Item excluído com sucesso!")

            # Verify item is removed from table
            print("Verifying item is removed from the table...")
            expect(page.locator(f'tr:has-text("{new_item_name}")')).not_to_be_visible()
            print("Item deleted successfully.")

            print("\n✅ CRUD Test for Items completed successfully!")

        except Exception as e:
            print(f"\n❌ An error occurred: {e}")
            page.screenshot(path="error_screenshot.png")
            print("Screenshot saved to error_screenshot.png")
        finally:
            browser.close()

if __name__ == "__main__":
    run_test()
