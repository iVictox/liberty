document.querySelector('.toggle-password').addEventListener('click', function() {
    const pwd = document.getElementById('password');
    pwd.type = pwd.type === 'password' ? 'text' : 'password';
});
function handleSubmit(e) {
    e.preventDefault();
    const username = document.getElementById('username').value.trim();
    alert('Bienvenido, ' + username);
}
