const { test, expect } = require('@playwright/test');

test.describe('Verificação da Tabela de Empréstimos', () => {

  test('deve carregar a página principal e exibir a tabela de empréstimos sem erros', async ({ page }) => {
    // Login
    await page.goto('http://localhost:89/login.php');
    await page.fill('input[name="nome"]', 'admin');
    await page.fill('input[name="senha"]', 'password');
    await page.click('button[type="submit"]');
    await expect(page).toHaveURL('http://localhost:89/index.php');

    // Verificar se o título da página está correto
    await expect(page).toHaveTitle('SGE - Página Principal');

    // Verificar se a tabela de empréstimos está visível
    const tabela = page.locator('#emprestimosTable');
    await expect(tabela).toBeVisible();

    // Tirar uma screenshot para verificação visual
    await page.screenshot({ path: '/home/jules/verification/tabela_emprestimos_verification.png' });
  });
});
