const { test, expect } = require('@playwright/test');

test.describe('Item CRUD Operations', () => {
  let uniqueItemName;

  test.beforeEach(async ({ page }) => {
    // Use um timestamp para garantir que o nome do item seja único em cada execução
    const timestamp = Date.now();
    uniqueItemName = `Notebook Dell ${timestamp}`;

    // Login
    await page.goto('http://localhost:89/login.php');
    await page.fill('input[name="nome"]', 'admin');
    await page.fill('input[name="senha"]', 'password');
    await page.click('button[type="submit"]');
    await expect(page).toHaveURL('http://localhost:89/index.php');

    // A navegação agora acontece dentro do teste
  });

  test('deve criar, editar e excluir um item com sucesso', async ({ page }) => {
    // Navegar para a página de gerenciamento de itens
    await page.click('a:has-text("Gerenciar Itens")');
    await expect(page).toHaveURL('http://localhost:89/gerenciar_itens.php');

    // Desabilitar o DataTables para simplificar a interação
    await page.evaluate(() => {
      // @ts-ignore
      if ($.fn.dataTable.isDataTable('#tabelaItens')) {
        // @ts-ignore
        $('#tabelaItens').DataTable().destroy();
      }
    });
    await page.waitForTimeout(500); // Pequena pausa para garantir que a destruição foi concluída

    // --- ETAPA DE CRIAÇÃO ---
    await page.click('button:has-text("Adicionar Novo Item")');
    await page.fill('input[name="nome"]', uniqueItemName);
    await page.fill('input[name="quantidade"]', '10');

    // Lidar com o SweetAlert de confirmação
    page.once('dialog', async dialog => {
        expect(dialog.message()).toContain('Item cadastrado com sucesso!');
        await dialog.accept();
    });
    await page.click('button[type="submit"]');

    // Aguardar o redirecionamento e verificar o novo item na tabela
    await page.waitForURL('http://localhost:89/gerenciar_itens.php');

    // Esperar pelo SweetAlert e fechá-lo
    await expect(page.locator('.swal2-popup')).toBeVisible();
    await page.locator('.swal2-confirm').click();
    await expect(page.locator('.swal2-popup')).not.toBeVisible();

    // Desabilitar DataTables novamente após o redirecionamento
    await page.evaluate(() => {
      // @ts-ignore
      if ($.fn.dataTable.isDataTable('#tabelaItens')) {
        // @ts-ignore
        $('#tabelaItens').DataTable().destroy();
      }
    });
    await expect(page.locator(`tr:has-text("${uniqueItemName}")`)).toBeVisible();


    // --- ETAPA DE EDIÇÃO ---
    const itemRow = page.locator(`tr:has-text("${uniqueItemName}")`);
    await itemRow.locator('a:has-text("Editar")').click();

    await expect(page).toHaveURL(/editar_item\.php\?id=\d+/);
    const updatedItemName = `${uniqueItemName}-editado`;
    await page.fill('input[name="nome"]', updatedItemName);
    await page.fill('input[name="quantidade"]', '15');

    page.once('dialog', async dialog => {
        expect(dialog.message()).toContain('Item atualizado com sucesso!');
        await dialog.accept();
    });
    await page.click('button[type="submit"]');

    // Aguardar o redirecionamento e verificar a edição
    await page.waitForURL('http://localhost:89/gerenciar_itens.php');

    // Esperar pelo SweetAlert e fechá-lo
    await expect(page.locator('.swal2-popup')).toBeVisible();
    await page.locator('.swal2-confirm').click();
    await expect(page.locator('.swal2-popup')).not.toBeVisible();

    await page.evaluate(() => {
        // @ts-ignore
      if ($.fn.dataTable.isDataTable('#tabelaItens')) {
        // @ts-ignore
        $('#tabelaItens').DataTable().destroy();
      }
    });
    await expect(page.locator(`tr:has-text("${updatedItemName}")`)).toBeVisible();
    await expect(page.locator(`//td[normalize-space(.) = "${uniqueItemName}"]`)).not.toBeVisible();


    // --- ETAPA DE EXCLUSÃO ---
    const updatedItemRow = page.locator(`tr:has-text("${updatedItemName}")`);

    // Clicar no botão de exclusão que aciona o SweetAlert
    await updatedItemRow.locator('.excluir-btn').click();

    // Esperar pelo SweetAlert de confirmação e clicar em "Sim, excluir!"
    await expect(page.locator('.swal2-popup')).toBeVisible();
    await page.locator('button.swal2-confirm').click();

    // Esperar pelo SweetAlert de sucesso e clicar em "OK"
    await expect(page.locator('.swal2-popup')).toBeVisible();
    await page.locator('button.swal2-confirm').click();

    // Aguardar o recarregamento da página (implícito pelo alerta) e a remoção do alerta
    await page.waitForURL('http://localhost:89/gerenciar_itens.php');
    await expect(page.locator('.swal2-popup')).not.toBeVisible();

    // Verificar se o item não está mais na tabela
    await page.waitForTimeout(1000); // Pausa para garantir que a UI atualizou
    await expect(page.locator(`tr:has-text("${updatedItemName}")`)).not.toBeVisible();

    // Tirar screenshot da tela final
    await page.screenshot({ path: '/home/jules/verification/crud_workflow_verification.png' });
  });
});
