import Joi from 'joi';

const joiOptions = {
    abortEarly: false,
    allowUnknown: true,
    stripUnknown: false,
};

const staffContractValues = ['FULL_TIME', 'PART_TIME', 'PROBATION', 'VISITING'];
const staffSalaryValues = ['MONTHLY', 'HOURLY', 'PER_SESSION', 'FIXED_ALLOWANCE'];
const avatarExtensions = ['jpg', 'jpeg', 'png', 'webp'];
const cvExtensions = ['pdf', 'doc', 'docx'];
const maxAvatarSize = 2 * 1024 * 1024;
const maxCvSize = 15 * 1024 * 1024;

function isFileObject(value) {
    return typeof File !== 'undefined' && value instanceof File;
}

function isEmptyFile(value) {
    return isFileObject(value) && value.size === 0 && value.name === '';
}

function optionalFile(allowedExtensions, maxSize, invalidTypeMessage, invalidSizeMessage) {
    return Joi.any().custom((value, helpers) => {
        if (value == null || value === '' || isEmptyFile(value)) {
            return null;
        }

        if (!isFileObject(value)) {
            return helpers.message('Tệp tải lên không hợp lệ.');
        }

        const extension = value.name.split('.').pop()?.toLowerCase() ?? '';

        if (!allowedExtensions.includes(extension)) {
            return helpers.message(invalidTypeMessage);
        }

        if (value.size > maxSize) {
            return helpers.message(invalidSizeMessage);
        }

        return value;
    });
}

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
        name: Joi.string().trim().max(255).pattern(/^[^0-9]*$/).required().messages({
            'string.empty': 'Vui lòng nhập họ và tên.',
            'string.max': 'Họ và tên không được vượt quá 255 ký tự.',
            'string.pattern.base': 'Họ và tên không được chứa chữ số.',
            'any.required': 'Vui lòng nhập họ và tên.',
        }),
        email: Joi.string().trim().email({ tlds: { allow: false } }).required().messages({
            'string.empty': 'Vui lòng nhập email.',
            'string.email': 'Email không đúng định dạng.',
            'any.required': 'Vui lòng nhập email.',
        }),
        phone: Joi.string().trim().pattern(/^[0-9]{10}$/).required().messages({
            'string.empty': 'Vui lòng nhập số điện thoại.',
            'string.pattern.base': 'Số điện thoại phải là 10 chữ số hợp lệ.',
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
    hocVien: Joi.object({
        email: Joi.string().trim().email({ tlds: { allow: false } }).max(100).required().messages({
            'string.empty': 'Vui lòng nhập email.',
            'string.email': 'Email không đúng định dạng.',
            'string.max': 'Email không được vượt quá 100 ký tự.',
            'any.required': 'Vui lòng nhập email.',
        }),
        hoTen: Joi.string().trim().max(100).pattern(/^[^0-9]*$/).required().messages({
            'string.empty': 'Vui lòng nhập họ và tên.',
            'string.max': 'Họ và tên không được vượt quá 100 ký tự.',
            'string.pattern.base': 'Họ và tên không được chứa chữ số.',
            'any.required': 'Vui lòng nhập họ và tên.',
        }),
        soDienThoai: Joi.string().trim().pattern(/^[0-9]{10}$/).allow('', null).messages({
            'string.pattern.base': 'Số điện thoại phải là 10 chữ số.',
        }),
        zalo: Joi.string().trim().pattern(/^[0-9]{10}$/).allow('', null).messages({
            'string.pattern.base': 'SĐT Zalo phải là 10 chữ số.',
        }),
        cccd: Joi.string().trim().max(20).allow('', null).messages({
            'string.max': 'CCCD không được vượt quá 20 ký tự.',
        }),
        nguoiGiamHo: Joi.string().trim().max(100).pattern(/^[^0-9]*$/).allow('', null).messages({
            'string.max': 'Họ tên người giám hộ không được vượt quá 100 ký tự.',
            'string.pattern.base': 'Họ tên người giám hộ không được chứa chữ số.',
        }),
        sdtGuardian: Joi.string().trim().pattern(/^[0-9]{10}$/).allow('', null).messages({
            'string.pattern.base': 'SĐT người giám hộ phải là 10 chữ số.',
        }),
        matKhau: Joi.string().min(8).allow('', null).messages({
            'string.min': 'Mật khẩu phải có ít nhất 8 ký tự.',
        }),
        matKhau_confirmation: Joi.string().valid(Joi.ref('matKhau')).allow('', null).messages({
            'any.only': 'Xác nhận mật khẩu không khớp.',
        }),
    }),
    nhanSu: Joi.object({
        email: Joi.string().trim().email({ tlds: { allow: false } }).max(100).required().messages({
            'string.empty': 'Vui lòng nhập email.',
            'string.email': 'Email không đúng định dạng.',
            'string.max': 'Email không được vượt quá 100 ký tự.',
            'any.required': 'Vui lòng nhập email.',
        }),
        trangThai: Joi.when('$requiresAccountStatus', {
            is: true,
            then: Joi.string().trim().valid('0', '1').required().messages({
                'string.empty': 'Vui lòng chọn trạng thái tài khoản.',
                'any.only': 'Trạng thái tài khoản không hợp lệ.',
                'any.required': 'Vui lòng chọn trạng thái tài khoản.',
            }),
            otherwise: Joi.string().trim().valid('0', '1').allow('', null).messages({
                'any.only': 'Trạng thái tài khoản không hợp lệ.',
            }),
        }),
        hoTen: Joi.string().trim().max(100).pattern(/^[^0-9]*$/).required().messages({
            'string.empty': 'Vui lòng nhập họ và tên.',
            'string.max': 'Họ và tên không được vượt quá 100 ký tự.',
            'string.pattern.base': 'Họ và tên không được chứa chữ số.',
            'any.required': 'Vui lòng nhập họ và tên.',
        }),
        anhDaiDien: optionalFile(
            avatarExtensions,
            maxAvatarSize,
            'Chỉ chấp nhận định dạng JPG, PNG, WEBP.',
            'Ảnh đại diện không được vượt quá 2MB.'
        ),
        ngaySinh: Joi.date().max('now').allow('', null).messages({
            'date.base': 'Ngày sinh không hợp lệ.',
            'date.max': 'Ngày sinh không được lớn hơn hôm nay.',
        }),
        gioiTinh: Joi.string().trim().valid('0', '1', '2').allow('', null).messages({
            'any.only': 'Giới tính không hợp lệ.',
        }),
        soDienThoai: Joi.string().trim().pattern(/^[0-9]{10}$/).allow('', null).messages({
            'string.pattern.base': 'Số điện thoại phải là 10 chữ số.',
        }),
        zalo: Joi.string().trim().pattern(/^[0-9]{10}$/).allow('', null).messages({
            'string.pattern.base': 'SĐT Zalo phải là 10 chữ số.',
        }),
        cccd: Joi.string().trim().pattern(/^[0-9]{9,12}$/).allow('', null).messages({
            'string.pattern.base': 'CCCD/CMND phải là 9 đến 12 chữ số.',
        }),
        diaChi: Joi.string().trim().max(255).allow('', null).messages({
            'string.max': 'Địa chỉ không được vượt quá 255 ký tự.',
        }),
        chucVu: Joi.string().trim().max(50).allow('', null).messages({
            'string.max': 'Chức vụ không được vượt quá 50 ký tự.',
        }),
        chuyenMon: Joi.string().trim().max(80).allow('', null).messages({
            'string.max': 'Chuyên môn không được vượt quá 80 ký tự.',
        }),
        bangCap: Joi.string().trim().max(80).allow('', null).messages({
            'string.max': 'Bằng cấp không được vượt quá 80 ký tự.',
        }),
        hocVi: Joi.string().trim().max(80).allow('', null).messages({
            'string.max': 'Học vị / chứng chỉ không được vượt quá 80 ký tự.',
        }),
        loaiHopDong: Joi.string().trim().required().messages({
            'string.empty': 'Vui lòng chọn loại hợp đồng.',
            'any.only': 'Loại hợp đồng không hợp lệ.',
            'any.required': 'Vui lòng chọn loại hợp đồng.',
        }).valid(...staffContractValues),
        ngayVaoLam: Joi.date().allow('', null).messages({
            'date.base': 'Ngày vào làm không hợp lệ.',
        }),
        coSoId: Joi.string().trim().min(1).required().messages({
            'string.empty': 'Vui lòng chọn cơ sở làm việc.',
            'string.min': 'Vui lòng chọn cơ sở làm việc.',
            'any.required': 'Vui lòng chọn cơ sở làm việc.',
        }),
        matKhau: Joi.string().min(8).allow('', null).messages({
            'string.min': 'Mật khẩu phải có ít nhất 8 ký tự.',
        }),
        matKhau_confirmation: Joi.string().valid(Joi.ref('matKhau')).allow('', null).messages({
            'any.only': 'Xác nhận mật khẩu không khớp.',
        }),
        nhanSuMauQuyDinhId: Joi.when('$requiresInitialStaffSetup', {
            is: true,
            then: Joi.alternatives().try(
                Joi.string().trim().min(1),
                Joi.number().integer().min(1)
            ).required().messages({
                'alternatives.match': 'Vui lòng chọn mẫu quy định áp dụng.',
                'any.required': 'Vui lòng chọn mẫu quy định áp dụng.',
            }),
            otherwise: Joi.alternatives().try(
                Joi.string().trim().min(1),
                Joi.number().integer().min(1)
            ).allow('', null).messages({
                'alternatives.match': 'Mẫu quy định không hợp lệ.',
            }),
        }),
        loaiLuong: Joi.when('$requiresInitialStaffSetup', {
            is: true,
            then: Joi.string().trim().valid(...staffSalaryValues).required().messages({
                'string.empty': 'Vui lòng chọn loại lương.',
                'any.only': 'Loại lương không hợp lệ.',
                'any.required': 'Vui lòng chọn loại lương.',
            }),
            otherwise: Joi.string().trim().valid(...staffSalaryValues).allow('', null).messages({
                'any.only': 'Loại lương không hợp lệ.',
            }),
        }),
        luongChinh: Joi.when('$requiresInitialStaffSetup', {
            is: true,
            then: Joi.number().required().min(0).messages({
                'number.base': 'Lương chính phải là số.',
                'number.min': 'Lương chính phải lớn hơn hoặc bằng 0.',
                'any.required': 'Vui lòng nhập lương chính.',
            }),
            otherwise: Joi.number().min(0).allow('', null).messages({
                'number.base': 'Lương chính phải là một số hợp lệ.',
                'number.min': 'Lương chính không được nhỏ hơn 0.',
            }),
        }),
        hieuLucTu: Joi.when('$requiresInitialStaffSetup', {
            is: true,
            then: Joi.date().required().messages({
                'date.base': 'Ngày hiệu lực không hợp lệ.',
                'any.required': 'Vui lòng chọn ngày hiệu lực.',
            }),
            otherwise: Joi.date().allow('', null).messages({
                'date.base': 'Ngày hiệu lực không hợp lệ.',
            }),
        }),
        cvXinViec: optionalFile(
            cvExtensions,
            maxCvSize,
            'CV chỉ hỗ trợ định dạng PDF, DOC hoặc DOCX.',
            'CV không được vượt quá 15MB.'
        ),
        cvTenHienThi: Joi.string().trim().max(150).allow('', null).messages({
            'string.max': 'Tên hiển thị CV không được vượt quá 150 ký tự.',
        }),
        cvGhiChu: Joi.string().allow('', null),
        ghiChu: Joi.string().allow('', null),
        ghiChuHoSo: Joi.string().allow('', null),
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
            continue;
        }

        if (Array.isArray(data[name])) {
            data[name].push(value);
            continue;
        }

        data[name] = [data[name], value];
    }

    return data;
}

function buildValidationContext(form) {
    return {
        requiresAccountStatus: !!form.querySelector('[name="trangThai"]'),
        requiresInitialStaffSetup: !!form.querySelector('[name="nhanSuMauQuyDinhId"]'),
    };
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

function isStaffForm(input) {
    return !!input.closest('.staff-control');
}

function showFieldError(form, fieldName, message) {
    const input = form.querySelector(`[name="${fieldName}"]`);

    if (!input) {
        return;
    }

    input.classList.add('is-invalid');

    if (isStaffForm(input)) {
        const feedback = document.createElement('span');
        feedback.className = 'staff-error joi-feedback';
        feedback.textContent = message;

        const staffControl = input.closest('.staff-control');
        staffControl.appendChild(feedback);
    } else {
        const feedback = document.createElement('div');
        feedback.className = 'invalid-feedback d-block joi-feedback';
        feedback.textContent = message;

        getErrorAnchor(input).insertAdjacentElement('afterend', feedback);
    }
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
    const { error, value } = schema.validate(payload, {
        ...joiOptions,
        context: buildValidationContext(form),
    });

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
        if (!validateForm(form)) {
            event.preventDefault();
            event.stopImmediatePropagation();
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('form[data-joi-schema]').forEach(bindForm);
});

window.FiveGeniusValidation = {
    validateForm,
    schemas: schemas,
};
