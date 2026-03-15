<div class="tuition-subnav">
    <a href="{{ route('home.student.tuition.debts') }}"
        class="tuition-subnav__link {{ $active === 'debts' ? 'is-active' : '' }}">
        <i class="fas fa-wallet"></i>
        <span>Tra cứu công nợ</span>
    </a>
    <a href="{{ route('home.student.tuition.receipts') }}"
        class="tuition-subnav__link {{ $active === 'receipts' ? 'is-active' : '' }}">
        <i class="fas fa-receipt"></i>
        <span>Phiếu thu tổng hợp</span>
    </a>
    <a href="{{ route('home.student.tuition.payments') }}"
        class="tuition-subnav__link {{ $active === 'payments' ? 'is-active' : '' }}">
        <i class="fas fa-credit-card"></i>
        <span>Thanh toán trực tuyến</span>
    </a>
</div>
