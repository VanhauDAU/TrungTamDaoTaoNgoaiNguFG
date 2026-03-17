import Joi from 'joi';

const joiOptions = {
    abortEarly: false,
    allowUnknown: true,
    stripUnknown: false,
};

const schemas = {
    login: Joi.object({
        taiKhoan: Joi.string().trim().required().messages({
            'string.empty': 'Vui lòng nhập tài khoản hoặc email.',
            'any.required': 'Vui lòng nhập tài khoản hoặc email.',
        }),
        password: Joi.string().min(8).required().messages({
            'string.empty': 'Vui lòng nhập mật khẩu.',
            'string.min': 'Mật khẩu phải có ít nhất 8 ký tự.',
            'any.required': 'Vui lòng nhập mật khẩu.',
        }),
    }),
    register: Joi.object({
        name: Joi.string().trim().max(255).required().messages({
            'string.empty': 'Vui lòng nhập họ và tên.',
            'string.max': 'Họ và tên không được vượt quá 255 ký tự.',
            'any.required': 'Vui lòng nhập họ và tên.',
        }),
        email: Joi.string().trim().email({ tlds: { allow: false } }).required().messages({
            'string.empty': 'Vui lòng nhập email.',
            'string.email': 'Email không đúng định dạng.',
            'any.required': 'Vui lòng nhập email.',
        }),
        phone: Joi.string().trim().length(10).required().messages({
            'string.empty': 'Vui lòng nhập số điện thoại.',
            'string.length': 'Số điện thoại phải có 10 ký tự.',
            'any.required': 'Vui lòng nhập số điện thoại.',
        }),
        password: Joi.string().min(8).required().messages({
            'string.empty': 'Vui lòng nhập mật khẩu.',
            'string.min': 'Mật khẩu phải có ít nhất 8 ký tự.',
            'any.required': 'Vui lòng nhập mật khẩu.',
        }),
        password_confirmation: Joi.string().required().valid(Joi.ref('password')).messages({
            'string.empty': 'Vui lòng xác nhận mật khẩu.',
            'any.only': 'Xác nhận mật khẩu không khớp.',
            'any.required': 'Vui lòng xác nhận mật khẩu.',
        }),
    }),
    forgotPassword: Joi.object({
        email: Joi.string().trim().email({ tlds: { allow: false } }).required().messages({
            'string.empty': 'Vui lòng nhập email.',
            'string.email': 'Email không đúng định dạng.',
            'any.required': 'Vui lòng nhập email.',
        }),
    }),
    resetPassword: Joi.object({
        email: Joi.string().trim().email({ tlds: { allow: false } }).required().messages({
            'string.empty': 'Vui lòng nhập email.',
            'string.email': 'Email không đúng định dạng.',
            'any.required': 'Vui lòng nhập email.',
        }),
        password: Joi.string().min(8).required().messages({
            'string.empty': 'Vui lòng nhập mật khẩu mới.',
            'string.min': 'Mật khẩu mới phải có ít nhất 8 ký tự.',
            'any.required': 'Vui lòng nhập mật khẩu mới.',
        }),
        password_confirmation: Joi.string().required().valid(Joi.ref('password')).messages({
            'string.empty': 'Vui lòng xác nhận mật khẩu mới.',
            'any.only': 'Xác nhận mật khẩu không khớp.',
            'any.required': 'Vui lòng xác nhận mật khẩu mới.',
        }),
    }),
    forceChangePassword: Joi.object({
        new_password: Joi.string().min(8).required().messages({
            'string.empty': 'Vui lòng nhập mật khẩu mới.',
            'string.min': 'Mật khẩu mới phải có ít nhất 8 ký tự.',
            'any.required': 'Vui lòng nhập mật khẩu mới.',
        }),
        new_password_confirmation: Joi.string().required().valid(Joi.ref('new_password')).messages({
            'string.empty': 'Vui lòng xác nhận mật khẩu mới.',
            'any.only': 'Xác nhận mật khẩu không khớp.',
            'any.required': 'Vui lòng xác nhận mật khẩu mới.',
        }),
    }),
    studentChangePassword: Joi.object({
        current_password: Joi.string().required().messages({
            'string.empty': 'Vui lòng nhập mật khẩu hiện tại.',
            'any.required': 'Vui lòng nhập mật khẩu hiện tại.',
        }),
        new_password: Joi.string().min(8).required().messages({
            'string.empty': 'Vui lòng nhập mật khẩu mới.',
            'string.min': 'Mật khẩu mới phải có ít nhất 8 ký tự.',
            'any.required': 'Vui lòng nhập mật khẩu mới.',
        }),
        new_password_confirmation: Joi.string().required().valid(Joi.ref('new_password')).messages({
            'string.empty': 'Vui lòng xác nhận mật khẩu mới.',
            'any.only': 'Xác nhận mật khẩu không khớp.',
            'any.required': 'Vui lòng xác nhận mật khẩu mới.',
        }),
    }),
    room: Joi.object({
        tenPhong: Joi.string().trim().max(50).required().messages({
            'string.empty': 'Vui lòng nhập tên phòng.',
            'string.max': 'Tên phòng không được vượt quá 50 ký tự.',
            'any.required': 'Vui lòng nhập tên phòng.'
        }),
        khuBlock: Joi.string().trim().max(50).allow('', null).messages({
            'string.max': 'Tên block không được vượt quá 50 ký tự.'
        }),
        tang: Joi.number().integer().min(0).max(50).allow('', null).messages({
            'number.base': 'Tầng phải là một số hợp lệ.',
            'number.min': 'Tầng không được nhỏ hơn 0.',
            'number.max': 'Tầng tối đa là 50.'
        }),
        sucChua: Joi.number().integer().min(1).max(999).allow('', null).messages({
            'number.base': 'Sức chứa phải là một số.',
            'number.min': 'Sức chứa tối thiểu là 1.',
            'number.max': 'Sức chứa tối đa là 999.'
        }),
        trangThai: Joi.number().valid(0, 1, 3).required().messages({
            'any.only': 'Trạng thái không hợp lệ.'
        }),
        trangThietBi: Joi.string().trim().max(500).allow('', null).messages({
            'string.max': 'Ghi chú thiết bị quá dài.'
        })
    }),
};

function getSchema(form) {
    const schemaName = form.dataset.joiSchema;

    if (!schemaName || !schemas[schemaName]) {
        return null;
    }

    return schemas[schemaName];
}

function normalizeFormData(form) {
    const data = {};

    for (const [name, value] of new FormData(form).entries()) {
        if (!(name in data)) {
            data[name] = value;
        }
    }

    return data;
}

function clearErrors(form) {
    form.querySelectorAll('.is-invalid').forEach((element) => {
        element.classList.remove('is-invalid');
    });

    form.querySelectorAll('.joi-feedback').forEach((element) => {
        element.remove();
    });
}

function getErrorAnchor(input) {
    return input.closest('.password_box') || input;
}

function showFieldError(form, fieldName, message) {
    const input = form.querySelector(`[name="${fieldName}"]`);

    if (!input) {
        return;
    }

    input.classList.add('is-invalid');

    const feedback = document.createElement('div');
    feedback.className = 'invalid-feedback d-block joi-feedback';
    feedback.textContent = message;

    getErrorAnchor(input).insertAdjacentElement('afterend', feedback);
}

function applyNormalizedValues(form, value) {
    Object.entries(value).forEach(([fieldName, fieldValue]) => {
        const input = form.querySelector(`[name="${fieldName}"]`);

        if (!input || typeof fieldValue !== 'string') {
            return;
        }

        input.value = fieldValue;
    });
}

function validateForm(form) {
    const schema = getSchema(form);

    if (!schema) {
        return true;
    }

    clearErrors(form);

    const payload = normalizeFormData(form);
    const { error, value } = schema.validate(payload, joiOptions);

    if (!error) {
        applyNormalizedValues(form, value);
        return true;
    }

    const shownFields = new Set();

    error.details.forEach((detail) => {
        const [fieldName] = detail.path;

        if (!fieldName || shownFields.has(fieldName)) {
            return;
        }

        shownFields.add(fieldName);
        showFieldError(form, fieldName, detail.message);
    });

    return false;
}

function bindForm(form) {
    form.addEventListener('submit', (event) => {
        if (validateForm(form)) {
            return;
        }

        event.preventDefault();
        event.stopPropagation();
    });
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('form[data-joi-schema]').forEach(bindForm);
});

window.FiveGeniusValidation = {
    validateForm,
    schemas: schemas,
};
