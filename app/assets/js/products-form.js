// importa o objeto user de main.js
// user contém os dados do usuário logado
// que, se existir, fica armazenado no localStorage
import { user } from './main.js';

// ######################################################
// Configuração inicial da página
// ######################################################

// busca todos os parâmetros da URL (query string)
const params = new URLSearchParams(window.location.search);

// obtem o ID do produto (se não existir, retorna null)
const productId = params.get('productId');

// seleciona o título da página
const pageTitle = document.querySelector('#page-title');

// se productId existir (não for null)
if (productId) {
    // se houver, estamos editando um produto existente
    pageTitle.textContent = 'Editar Produto';
    loadProductData(productId); // carrega os dados do produto
} else {
    // caso contrário, estamos adicionando um novo produto
    pageTitle.textContent = 'Adicionar Produto';
}

// ######################################################
// Evento de envio do formulário
// ######################################################

// seleciona o formulário
const productForm = document.querySelector('#product-form');

// seleciona o botão de envio
const submitButton = document.querySelector('#submit-button');

// adiciona evento para o envio do formulário
productForm.addEventListener('submit', async (event) => {
    // previne o comportamento padrão de envio do formulário    
    event.preventDefault(); 

    // desabilita o botão para evitar múltiplos envios
    submitButton.disabled = true;

    // coleta os dados do formulário
    const formData = new FormData(productForm);
    
    // cria um objeto com os dados do produto, obtidos do formulário
    // e já converte os campos numéricos para os tipos corretos
    const productData = {
        name: formData.get('name').trim(),
        stock: parseInt(formData.get('stock')),
        price: parseFloat(formData.get('price')),
        active: formData.get('active') === 'on' ? 1 : 0,
        userId: user.id
    };

    // AQUI, PODERIAM SER FEITAS VALIDAÇÕES CLIENT-SIDE 
    // ANTES DE ENVIAR PARA A API, EVITANDO REQUISIÇÕES DESNECESSÁRIAS.
    // POR EXEMPLO, UMA DELAS PODERIA SER:
    // if (!productData.name) {
    //     alert('O nome do produto é obrigatório.');
    //     submitButton.disabled = false;
    //     return;
    // }
    // NÃO FAREMOS, POIS QUEREMOS TESTAR A VALIDAÇÃO DA API.

    // agora, vamos preparar os parâmetros da requisição
    // que serão diferentes para criação e para edição
    // e é a presença do productId que define isso

    // define o método HTTP, 
    // sendo POST para criação e PUT para edição
    const method = productId ? 'PUT' : 'POST';

    // define a URL da API de acordo com a operação
    const url = productId 
    ? `/api/products/update.php?id=${productId}` 
    : '/api/products/add.php';

    // Agora, fazemos a requisição para a API
    // usamos try/catch para tratar possíveis erros alheios a API
    // como erros de rede ou outros
    try {
        // fazemos a requisição utilizando fetch
        // além da URL, passamos um objeto com o método, os cabeçalhos 
        // e o corpo da requisição, convertido em JSON 
        // como fetch é assíncrono, usamos await
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(productData)
        });

        // nesse ponto, temos certeza de que a resposta chegou
        // pois o fetch não lançou erro (iria para o catch)
        // e a resposta está completa, pois usamos await

        // como nossa API sempre retorna JSON,
        // devemos decodificar a resposta
        // o método .json() também é assíncrono, por isso usamos await
        const result = await response.json();


        // verifica se a resposta foi OK (códigos 2xx)
        if (response.ok) { 
            // Operação bem-sucedida
            // alert com mensagem de sucesso 
            // sabemos que a nossa API sempre retorna um campo 'message'
            alert(result.message);

            // redireciona para a página da listagem de produtos
            window.location.href = '/app/products/index.html';            
        } else {
            // Em caso de erro na API (código 4xx ou 5xx)
            // alert com mensagem de erro geral
            alert(result.message);

            // sabemos que nessas rotas de criação e edição
            // a nossa API retorna os erros de validação em um objeto 'errors'.
            // mas esse objeto não está presente em todos os erros,
            // por exemplo, nos erros 404 e 405 esse objeto não existe
            // então, verificamos se ele existe antes de tentar usá-lo
            if (result.errors) {
                // seleciona o contêiner de erros do formulário
                // e limpa qualquer conteúdo anterior
                const formErrors = document.getElementById('form-errors');
                formErrors.innerHTML = '';

                // obtém um array com as mensagens de erro
                // Object.values() retorna um array com
                // todos os valores das propriedades de um objeto
                // estude também: Object.keys() e Object.entries()
                const errors = Object.values(result.errors);

                // percorre os erros e cria um parágrafo para cada mensagem
                // exibindo-os no contêiner de erros
                errors.forEach(message => {
                    const errorItem = document.createElement('p');
                    errorItem.textContent = message;
                    formErrors.appendChild(errorItem);
                });
            }
        }
    } catch (error) {
        // em caso de erros não relacionados à API
        // como erros de rede ou outros
        alert('Erro ao salvar o produto');
        console.error(error);
    } finally {
        // reabilita o botão após o processamento
        submitButton.disabled = false;
    }
});

// ######################################################
// Evento do botão cancelar
// ######################################################

// seleciona o botão cancelar
const cancelButton = document.querySelector('#cancel-button');

// adiciona o evento de clique
cancelButton.addEventListener('click', (event) => {
    event.preventDefault();
    // redireciona para a página de lista de produtos
    window.location.href = '/app/products/index.html';
});

// ######################################################
// Busca dos dados do produto, no caso de edição
// ######################################################

// função para carregar os dados do produto para edição
async function loadProductData(productId) {
    // define a URL da API para obter os dados do produto
    const url = `/api/products/get.php?id=${productId}`;

    // usamos try/catch para tratar erros
    // não relacionados à API, como erros de rede ou outros
    try {
        // faz a requisição para a API
        // como fetch é assíncrono, usamos await
        const response = await fetch(url);

        // nesse ponto, temos certeza de que a resposta chegou
        // pois o fetch não lançou erro (iria para o catch)
        // e a resposta está completa, pois usamos await

        // decodifica a resposta JSON
        // o método .json() também é assíncrono, por isso usamos await
        const result = await response.json();

        // verifica se a resposta foi OK (códigos 2xx)
        if (response.ok) {
            // nessa rota, a API retorna o produto no campo 'product'
            const product = result.product;

            // preenche o formulário com os dados do produto
            productForm.name.value = product.name;
            productForm.stock.value = product.stock;
            productForm.price.value = product.price.toFixed(2);
            productForm.active.checked = product.active === 1;
        } else {
            // em caso de erro na API, exibe um alert com a mensagem geral
            alert(result.message);

            // caso o erro seja de produto não encontrado (404), 
            // redireciona para página da listagem de produtos
            if (response.status === 404) {
                window.location.href = '/app/products/index.html';
            }
        }
    } catch (error) {
        // em caso de erros não relacionados à API, 
        // como erros de rede ou outros
        alert('Erro ao carregar os dados do produto');
        console.error(error);

        // redireciona para a página da listagem de produtos
        window.location.href = '/app/products/index.html';
    }
}