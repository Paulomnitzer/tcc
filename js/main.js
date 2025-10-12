/**
 * JavaScript Principal do Sistema TCC
 * 
 * Este arquivo contém as funcionalidades JavaScript globais do sistema.
 * Organize aqui:
 * - Funções utilitárias
 * - Validações de formulário
 * - Interações da interface
 * - Configurações globais
 */

// ===== CONFIGURAÇÕES GLOBAIS =====
const CONFIG = {
    // URLs da aplicação
    BASE_URL: window.location.origin + '/projeto-tcc',
    API_URL: window.location.origin + '/projeto-tcc/api',
    
    // Configurações de interface
    ANIMATION_DURATION: 300,
    TOAST_DURATION: 5000,
    
    // Configurações de validação
    PASSWORD_MIN_LENGTH: 8,
    UPLOAD_MAX_SIZE: 5 * 1024 * 1024, // 5MB
};

// ===== FUNÇÕES UTILITÁRIAS =====

/**
 * Exibir notificação toast
 * @param {string} message - Mensagem a ser exibida
 * @param {string} type - Tipo da notificação (success, error, warning, info)
 */
function showToast(message, type = 'info') {
    // Remover toasts existentes
    const existingToasts = document.querySelectorAll('.toast-custom');
    existingToasts.forEach(toast => toast.remove());
    
    // Criar novo toast
    const toast = document.createElement('div');
    toast.className = `toast-custom alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
    toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    
    toast.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(toast);
    
    // Remover automaticamente após o tempo configurado
    setTimeout(() => {
        if (toast.parentNode) {
            toast.remove();
        }
    }, CONFIG.TOAST_DURATION);
}

/**
 * Validar email
 * @param {string} email - Email a ser validado
 * @returns {boolean} - True se válido, false caso contrário
 */
function validateEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

/**
 * Validar senha
 * @param {string} password - Senha a ser validada
 * @returns {object} - Objeto com resultado da validação
 */
function validatePassword(password) {
    const result = {
        valid: true,
        errors: []
    };
    
    if (password.length < CONFIG.PASSWORD_MIN_LENGTH) {
        result.valid = false;
        result.errors.push(`Senha deve ter pelo menos ${CONFIG.PASSWORD_MIN_LENGTH} caracteres`);
    }
    
    if (!/[A-Z]/.test(password)) {
        result.valid = false;
        result.errors.push('Senha deve conter pelo menos uma letra maiúscula');
    }
    
    if (!/[a-z]/.test(password)) {
        result.valid = false;
        result.errors.push('Senha deve conter pelo menos uma letra minúscula');
    }
    
    if (!/[0-9]/.test(password)) {
        result.valid = false;
        result.errors.push('Senha deve conter pelo menos um número');
    }
    
    return result;
}

/**
 * Formatar CPF
 * @param {string} cpf - CPF a ser formatado
 * @returns {string} - CPF formatado
 */
function formatCPF(cpf) {
    cpf = cpf.replace(/\D/g, '');
    return cpf.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
}

/**
 * Formatar telefone
 * @param {string} phone - Telefone a ser formatado
 * @returns {string} - Telefone formatado
 */
function formatPhone(phone) {
    phone = phone.replace(/\D/g, '');
    if (phone.length === 11) {
        return phone.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
    } else if (phone.length === 10) {
        return phone.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
    }
    return phone;
}

/**
 * Confirmar ação
 * @param {string} message - Mensagem de confirmação
 * @param {function} callback - Função a ser executada se confirmado
 */
function confirmAction(message, callback) {
    if (confirm(message)) {
        callback();
    }
}

// ===== VALIDAÇÕES DE FORMULÁRIO =====

/**
 * Validar formulário de login
 */
function validateLoginForm() {
    const email = document.getElementById('email');
    const password = document.getElementById('senha');
    let isValid = true;
    
    // Limpar erros anteriores
    clearFieldErrors();
    
    // Validar email
    if (!email.value.trim()) {
        showFieldError(email, 'Email é obrigatório');
        isValid = false;
    } else if (!validateEmail(email.value)) {
        showFieldError(email, 'Email inválido');
        isValid = false;
    }
    
    // Validar senha
    if (!password.value.trim()) {
        showFieldError(password, 'Senha é obrigatória');
        isValid = false;
    }
    
    return isValid;
}

/**
 * Validar formulário de cadastro
 */
function validateRegisterForm() {
    const nome = document.getElementById('nome');
    const email = document.getElementById('email');
    const senha = document.getElementById('senha');
    const confirmarSenha = document.getElementById('confirmar_senha');
    let isValid = true;
    
    // Limpar erros anteriores
    clearFieldErrors();
    
    // Validar nome
    if (!nome.value.trim()) {
        showFieldError(nome, 'Nome é obrigatório');
        isValid = false;
    }
    
    // Validar email
    if (!email.value.trim()) {
        showFieldError(email, 'Email é obrigatório');
        isValid = false;
    } else if (!validateEmail(email.value)) {
        showFieldError(email, 'Email inválido');
        isValid = false;
    }
    
    // Validar senha
    const passwordValidation = validatePassword(senha.value);
    if (!passwordValidation.valid) {
        showFieldError(senha, passwordValidation.errors.join('<br>'));
        isValid = false;
    }
    
    // Validar confirmação de senha
    if (senha.value !== confirmarSenha.value) {
        showFieldError(confirmarSenha, 'Senhas não conferem');
        isValid = false;
    }
    
    return isValid;
}

/**
 * Exibir erro em campo específico
 * @param {HTMLElement} field - Campo do formulário
 * @param {string} message - Mensagem de erro
 */
function showFieldError(field, message) {
    field.classList.add('is-invalid');
    
    // Remover feedback anterior se existir
    const existingFeedback = field.parentNode.querySelector('.invalid-feedback');
    if (existingFeedback) {
        existingFeedback.remove();
    }
    
    // Criar novo feedback
    const feedback = document.createElement('div');
    feedback.className = 'invalid-feedback';
    feedback.innerHTML = message;
    field.parentNode.appendChild(feedback);
}

/**
 * Limpar erros de todos os campos
 */
function clearFieldErrors() {
    const invalidFields = document.querySelectorAll('.is-invalid');
    const feedbacks = document.querySelectorAll('.invalid-feedback');
    
    invalidFields.forEach(field => field.classList.remove('is-invalid'));
    feedbacks.forEach(feedback => feedback.remove());
}

// ===== INTERAÇÕES DA INTERFACE =====

/**
 * Inicializar máscaras de input
 */
function initInputMasks() {
    // Máscara para CPF
    const cpfInputs = document.querySelectorAll('input[data-mask="cpf"]');
    cpfInputs.forEach(input => {
        input.addEventListener('input', function() {
            this.value = formatCPF(this.value);
        });
    });
    
    // Máscara para telefone
    const phoneInputs = document.querySelectorAll('input[data-mask="phone"]');
    phoneInputs.forEach(input => {
        input.addEventListener('input', function() {
            this.value = formatPhone(this.value);
        });
    });
}

/**
 * Inicializar tooltips do Bootstrap
 */
function initTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

/**
 * Inicializar popovers do Bootstrap
 */
function initPopovers() {
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
}

// ===== EVENTOS DO DOCUMENTO =====

// Executar quando o DOM estiver carregado
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar componentes
    initInputMasks();
    initTooltips();
    initPopovers();
    
    // Adicionar animação de fade-in aos cards
    const cards = document.querySelectorAll('.card');
    cards.forEach((card, index) => {
        setTimeout(() => {
            card.classList.add('fade-in');
        }, index * 100);
    });
    
    // Configurar validação de formulários
    const loginForm = document.querySelector('form[action*="login"]');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            if (!validateLoginForm()) {
                e.preventDefault();
            }
        });
    }
    
    const registerForm = document.querySelector('form[action*="cadastro"]');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            if (!validateRegisterForm()) {
                e.preventDefault();
            }
        });
    }
    
    // Configurar confirmação para ações perigosas
    const dangerButtons = document.querySelectorAll('.btn-danger[data-confirm]');
    dangerButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            const message = this.getAttribute('data-confirm') || 'Tem certeza que deseja continuar?';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });
});

// Executar quando a página estiver totalmente carregada
window.addEventListener('load', function() {
    // Ocultar loading se existir
    const loading = document.querySelector('.loading');
    if (loading) {
        loading.style.display = 'none';
    }
    
    // Exibir mensagem de boas-vindas se for primeira visita
    if (!localStorage.getItem('visited')) {
        showToast('Bem-vindo ao sistema!', 'success');
        localStorage.setItem('visited', 'true');
    }
});

// ===== EXPORTAR FUNÇÕES GLOBAIS =====
window.TCC = {
    showToast,
    validateEmail,
    validatePassword,
    formatCPF,
    formatPhone,
    confirmAction,
    CONFIG
};
