// importa o objeto user de main.js
// user contém os dados do usuário logado
// que, se existir, fica armazenado no localStorage
import { user } from './main.js';

// chamada inicial para buscar os produtos do usuário logado
// usa a função definida mais abaixo
// passando o ID do usuário logado
// que está disponível no objeto `user`
fetchProductsByUser(user.id);

// ######################################################
// Função para buscar produtos do usuário logado
// ######################################################

// como usaremos await dentro da função, ela deve ser async
async function fetchProductsByUser(userId) {

    // define a URL da API para buscar produtos do usuário
    const url = `/api/products/list.php?userId=${userId}`;

    // usamos try/catch para tratar possíveis erros alheios a API
    // como erros de rede ou outros
    try {
        // fazemos a requisição utilizando fetch
        // como fetch é assíncrono, usamos await para esperar a resposta
        const response = await fetch(url);

        // nesse ponto, temos certeza de que a resposta chegou
        // pois o fetch não lançou erro (iria para o catch)
        // e a resposta está completa, pois usamos await

        // processa a resposta da API, que está em formato JSON
        const result = await response.json();

        // se a resposta não for OK (códigos 4xx ou 5xx)
        if (!response.ok) {
            // exibe a mensagem retornada pela API
            // nossa API sempre retorna um campo 'message'
            alert(result.message);
            return;
        }
        // se a resposta for OK (2xx), chamamos a função para renderizar a tabela de produtos
        // a API retorna a lista de produtos no campo 'products' da resposta
        // então, passamos essa lista para a função displayProducts()
        displayProducts(result.products);
    } catch (error) {
        // em caso de erros não relacionados à API,
        // como erros de rede ou outros
        console.error(error);
        alert('Erro ao buscar produtos');
    }
}

// ######################################################
// Função para renderizar a tabela de produtos
// ######################################################

function displayProducts(products) {
    // seleciona o corpo da tabela onde os produtos serão exibidos
    const tableBody = document.querySelector('#table-body');

    // limpa o conteúdo atual da tabela
    tableBody.innerHTML = '';

    // Cria um fragmento para otimizar a adição de elementos
    const fragment = document.createDocumentFragment();

    // para cada produto na lista de produtos recebida como parâmetro
    products.forEach(product => {
        // cria e adiciona uma nova linha de tabela
        const row = document.createElement('tr');
        fragment.appendChild(row);

        // cria e adiciona na linha uma coluna para o nome do produto
        const nameCol = document.createElement('td');
        nameCol.textContent = product.name;
        row.appendChild(nameCol);

        // cria e adiciona na linha uma coluna para o estoque do produto
        const stockCol = document.createElement('td');
        stockCol.textContent = product.stock;
        row.appendChild(stockCol);

        // cria e adiciona na linha uma coluna para o preço do produto
        const priceCol = document.createElement('td');
        // formata o preço como moeda brasileira (R$)
        priceCol.textContent = product.price.toLocaleString('pt-BR', {
            style: 'currency',
            currency: 'BRL'
        });
        row.appendChild(priceCol);

        // cria e adiciona na linha a coluna para o status (ativo) do produto
        const activeCol = document.createElement('td');
        activeCol.textContent = product.active ? 'Sim' : 'Não';
        row.appendChild(activeCol);

        // cria e adiciona na linha uma coluna para as ações (editar, excluir etc.)
        const actionsCol = document.createElement('td');
        actionsCol.classList.add('actions');
        row.appendChild(actionsCol);

        // cria o botão de editar e adiciona na coluna de ações
        const editButton = document.createElement('button');
        editButton.textContent = '✏️';
        editButton.classList.add('btn-edit');
        editButton.title = 'Editar Produto';
        actionsCol.appendChild(editButton);

        // cria o botão de alterar ativo/inativo e adiciona na coluna de ações
        const toggleButton = document.createElement('button');
        toggleButton.textContent = '🔄️'
        toggleButton.classList.add('btn-toggle');
        toggleButton.title = 'Alterar Ativo/Inativo';
        actionsCol.appendChild(toggleButton);
        
        // cria o botão de excluir e adiciona na coluna de ações
        const deleteButton = document.createElement('button');
        deleteButton.textContent = '🗑️';
        deleteButton.classList.add('btn-delete');
        deleteButton.title = 'Excluir Produto';
        actionsCol.appendChild(deleteButton); 
        
        // EVENTOS DE CLIQUE DOS BOTÕES CRIADOS ACIMA

        // evento de clique para o botão de editar
        editButton.addEventListener('click', () => {
            // redireciona para a página de formulário de produtos
            // passando o ID do produto como parâmetro na URL
            document.location.href = `/app/products/form.html?productId=${product.id}`;
        });

        // evento de clique para o botão de alterar ativo/inativo
        toggleButton.addEventListener('click', () => {
            // chama a função para alternar o status do produto
            // passando o ID do produto como parâmetro
            toggleActive(product.id);
        });

        // evento de clique para o botão de excluir
        deleteButton.addEventListener('click', () => {
            // chama a função para excluir o produto
            // passando o ID do produto como parâmetro
            deleteProduct(product.id);
        });
    });

    // após criar todas as linhas,
    // adiciona no corpo da tabela o fragmento 
    // com todas as linhas de uma única vez
    tableBody.appendChild(fragment);
}

// ######################################################
// Função para alternar o status ativo/inativo do produto
// ######################################################

// como usaremos await dentro da função, ela deve ser async
async function toggleActive(productId) {
    // define a URL da rota da API para alternar o status do produto
    // o ID do produto é enviado como parâmetro na URL (query string)
    const url = `/api/products/toggle-active.php?id=${productId}`;

    // usamos try/catch para tratar possíveis erros alheios a API
    // como erros de rede ou outros
    try {
        // fazemos a requisição utilizando fetch
        // como fetch é assíncrono, usamos await para esperar a resposta
        // repara que nessa rota o método é PATCH
        const response = await fetch(url, {
            method: 'PATCH'
        });

        // nesse ponto, temos certeza de que a resposta chegou
        // pois o fetch não lançou erro (iria para o catch)
        // e a resposta está completa, pois usamos await

        // processa a resposta da API, que está em formato JSON
        const result = await response.json();

        // se a resposta não for OK (códigos 4xx ou 5xx)
        if (!response.ok) {
            // exibe a mensagem retornada pela API
            // nossa API sempre retorna um campo 'message'
            alert(result.message);
            return;
        }

        // se a resposta for OK (2xx)
        // busca novamente a lista de produtos do usuário
        // para atualizar a tabela com o novo status
        fetchProductsByUser(user.id);
    } catch (error) {
        // em caso de erros não relacionados à API,
        // como erros de rede ou outros
        console.error(error);
        alert('Erro ao alterar status do produto.');
    }
}

// ######################################################
// Função para excluir um produto
// ######################################################

// como usaremos await dentro da função, ela deve ser async
async function deleteProduct(productId) {

    // define a URL da rota da API para excluir o produto
    // o ID do produto é enviado como parâmetro na URL (query string)
    const url = `/api/products/delete.php?id=${productId}`;

    // usamos try/catch para tratar possíveis erros alheios a API
    // como erros de rede ou outros
    try {
        // fazemos a requisição utilizando fetch
        // como fetch é assíncrono, usamos await para esperar a resposta
        // repara que nessa rota o método é DELETE
        const response = await fetch(url, {
            method: 'DELETE'
        });

        // nesse ponto, temos certeza de que a resposta chegou
        // pois o fetch não lançou erro (iria para o catch)
        // e a resposta está completa, pois usamos await

        // processa a resposta da API, que está em formato JSON
        const result = await response.json();

        // se a resposta não for OK (códigos 4xx ou 5xx)
        if (!response.ok) {
            // exibe a mensagem retornada pela API
            // nossa API sempre retorna um campo 'message'
            alert(result.message);
            return
        }

        // se a resposta for OK (2xx), exibe uma mensagem de sucesso
        alert('Produto excluído com sucesso!');
        // e busca novamente a lista de produtos do usuário
        // para atualizar a tabela sem o produto excluído
        fetchProductsByUser(user.id);
    } catch (error) {
        // em caso de erros não relacionados à API,
        // como erros de rede ou outros
        console.error(error);
        alert('Erro ao excluir produto.');
    }
}

// ######################################################
// Evento de clique do botão "Adicionar Produto"
// ######################################################

// seleciona o botão de adicionar produto
const addButton = document.querySelector('#btn-add');

// adiciona o evento de clique
addButton.addEventListener('click', () => {
    // redireciona para a página de formulário de produtos
    document.location.href = '/app/products/form.html';
});