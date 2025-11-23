// se entrar na página de login, remove qualquer dado de usuário armazenado
// ou seja, faz o logout automático
localStorage.removeItem('user');

// ######################################################
// Evento de envio do formulário
// ######################################################

// seleciona o formulário
const loginForm = document.querySelector('#login-form');
// seleciona o elemento de mensagem de erro
const errorMessage = document.querySelector('#error-message');

// adiciona o evento para o envio do formulário
loginForm.addEventListener('submit', async (event) => {
    // previne o comportamento padrão de envio do formulário
    event.preventDefault(); 

    // coleta os dados do formulário
    const formData = new FormData(loginForm);
    
    // cria um objeto com os dados de login (credenciais)
    const credentials = {
        email: formData.get('email'),
        password: formData.get('password')
    };

    // Agora, faremos uma requisição para a rota de login daAPI

    // define a URL da API para login
    const url = '/api/users/login.php';

    // o método é POST e o cabeçalho indica que o corpo é JSON
    // serão definidos diretamente na chamada fetch abaixo
    // além disso, as credenciais são convertidas para JSON

    // usamos try/catch para tratar possíveis erros alheios a API
    // como erros de rede ou outros
    try {
        // fazemos a requisição utilizando fetch
        // como fetch é assíncrono, usamos await para esperar a resposta
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(credentials)
        });

        // nesse ponto, temos certeza de que a resposta chegou
        // pois o fetch não lançou erro (iria para o catch)
        // e a resposta está completa, pois usamos await

        // processa a resposta da API, que está em formato JSON
        const result = await response.json();
        
        // verifica se a resposta foi bem-sucedida (código 2xx)
        if (response.ok) {
            // Login bem-sucedido
            // armazena os dados do usuário no localStorage
            // nossa API retorna os dados do usuário no campo `user`
            localStorage.setItem('user', JSON.stringify(result.user));

            // redireciona para a página inicial da aplicação
            window.location.href = '/app/index.html';
        } else {
            // em caso de erro (códigos 4xx ou 5xx),
            // exibe a mensagem retornada pela API
            // no elemento da mensagem de erro
            errorMessage.textContent = result.message;
        }
    } catch (error) {
        // em caso de erros não relacionados à API,
        // como erros de rede ou outros
        console.error('Erro ao fazer login:', error);
        errorMessage.textContent = 'Erro ao fazer login';
    }
});
