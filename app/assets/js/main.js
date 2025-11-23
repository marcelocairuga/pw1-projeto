// Este módulo verifica se o usuário está logado.
// Para isso, tenta obter os dados salvos no localStorage.
// 
// Se existir a chave 'user', significa que o usuário está logado.
// O JSON armazenado é convertido em objeto e exportado,
// permitindo que outros módulos tenham acesso ao usuário logado.
// 
// Caso a chave 'user' não exista, o usuário não está autenticado,
// então redirecionamos para a página de login.
// 
// Todas as páginas que exigem autenticação devem importar este módulo.
// Assim garantimos que somente usuários logados possam acessá-las.

// tenta obter a chave 'user' no localStorage
const userData = localStorage.getItem('user');

// se a chave existir, converte o JSON para objeto
export const user = userData ? JSON.parse(userData) : null;
// o objeto `user` é exportado para ser usado nos módulos que importarem este

if (user) {
    // se user não for null, o usuário está logado
    // preenche as informações do header com os dados do usuário
    document.querySelector('#user-name').textContent = user.name;
    document.querySelector('#user-email').textContent = user.email;
} else {
    // se user for null, o usuário não está logado
    // redireciona para a página de login
    window.location.href = '/app/auth/login.html';
}

// Observação: nossa aplicação utiliza o mesmo cabeçalho (header)
// em todas as páginas autenticadas. Por isso, sempre existem
// os elementos #user-name e #user-email no HTML.
// Assim, podemos preenchê-los diretamente com os dados do usuário.
// Em outras aplicações, essa estrutura pode ser diferente e
// talvez não seja possível preencher essas informações aqui.
