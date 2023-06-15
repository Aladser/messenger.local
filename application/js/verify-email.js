let message = document.querySelector('#message');
let backBtn = document.querySelector('#back-btn');

let className = message.innerHTML === 'Электронная почта подтверждена' ? 'bg-success' : 'bg-warning';
backBtn.classList.add(className);