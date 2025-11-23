// ######################################################
// Evento de envio do formulário
// ######################################################

// seleciona o elemento do formulário 
const registerForm = document.querySelector('#register-form');

// seleciona os elementos para mensagens de erro específicas dos campos
const errorName = document.querySelector('#error-name');
const errorEmail = document.querySelector('#error-email');
const errorPassword = document.querySelector('#error-password');

// seleciona o botão de envio do formulário
const submitButton = document.querySelector('#submit-button');

// adiciona o evento para o envio do formulário
registerForm.addEventListener('submit', async (event) => {
    // previne o comportamento padrão de envio do formulário
    event.preventDefault(); 

    // desabilita o botão para evitar múltiplos envios
    submitButton.disabled = true; 

    // limpa mensagens de erro anteriores
    errorName.textContent = '';
    errorEmail.textContent = '';
    errorPassword.textContent = '';

    // coleta os dados do formulário
    const formData = new FormData(registerForm);
    
    // cria um objeto com os dados do usuário
    const userData = {
        name: formData.get('name').trim(),
        email: formData.get('email').trim(),
        password: formData.get('password')
    };

    // AQUI PODERIA SER FEITA UMA VALIDAÇÃO CLIENT-SIDE 
    // ANTES DE ENVIAR PARA A API, EVITANDO REQUISIÇÕES DESNECESSÁRIAS.
    // POR EXEMPLO, UMA DELAS PODERIA SER:
    // if (!userData.name) {
    //     alert('O nome é obrigatório.');
    //     submitButton.disabled = false;
    //     return;
    // }
    // NÃO FAREMOS, POIS QUEREMOS TESTAR A VALIDAÇÃO DA API.

    // Agora, faremos uma requisição para a rota de registro de usuários da API

    // define a URL da API para registro
    const url = '/api/users/register.php';

    // o método é POST e o cabeçalho indica que o corpo é JSON
    // serão definidos diretamente na chamada fetch abaixo
    // além disso, os dados do usuário são convertidos para JSON

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
            body: JSON.stringify(userData)
        });

        // nesse ponto, temos certeza de que a resposta chegou
        // pois o fetch não lançou erro (iria para o catch)
        // e a resposta está completa, pois usamos await

        // processa a resposta da API que está em formato JSON
        const result = await response.json();

        if (response.ok) { 
            // Registro bem-sucedido (código 2xx)
            // exibe uma mensagem de sucesso e redireciona para o login
            alert('Registro bem-sucedido! Agora você pode fazer login.');
            window.location.href = '/app/auth/login.html';            
        } else {
            // se a operação falhar (códigos 4xx ou 5xx)
            // exibe a mensagem geral retornada pela API
            // (a API sempre retorna um campo 'message')
            alert(result.message);

            // no caso de erros de validação,
            // a API retorna um campo 'errors' com os erros específicos
            // por isso, verificamos se ele existe antes de tentar usá-lo
            if (result.errors) {
                errorName.textContent = result.errors.name ?? '';
                errorEmail.textContent = result.errors.email ?? '';
                errorPassword.textContent = result.errors.password ?? '';                
            }
        }
    } catch (error) {
        // em caso de erros não relacionados à API,
        // como erros de rede ou outros
        console.error(error);
        alert('Erro ao processar o registro.');
    } finally {
        // reabilita o botão após o processamento
        submitButton.disabled = false;
    }
});