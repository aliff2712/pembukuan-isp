@extends('layouts-main.app')

@section('title', 'Pembayaran Transaksi')

@section('content')
<style>
    .love-animation {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 50px;
    pointer-events: none;
    animation: lovePop 1s ease forwards;
    z-index: 9999;
}

@keyframes lovePop {
    0% {
        opacity: 0;
        transform: translate(-50%, -40%) scale(0.5);
    }
    30% {
        opacity: 1;
        transform: translate(-50%, -50%) scale(1.2);
    }
    100% {
        opacity: 0;
        transform: translate(-50%, -70%) scale(1);
    }
}
.heart-filter {
    position: fixed;
    font-size: 22px;
    pointer-events: none;
    animation: floatHeart 1s linear forwards;
    z-index: 9999;
}

@keyframes floatHeart {
    0%{
        transform: translateY(0) scale(0.8);
        opacity: 1;
    }
    100%{
        transform: translateY(-120px) scale(1.3);
        opacity: 0;
    }
}.love-animation {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 50px;
    pointer-events: none;
    animation: lovePop 1s ease forwards;
    z-index: 9999;
}

@keyframes lovePop {
    0% {
        opacity: 0;
        transform: translate(-50%, -40%) scale(0.5);
    }
    30% {
        opacity: 1;
        transform: translate(-50%, -50%) scale(1.2);
    }
    100% {
        opacity: 0;
        transform: translate(-50%, -70%) scale(1);
    }
}
.heart-filter, .confetti {
    position: fixed;
    pointer-events: none;
    z-index: 9999;
}

.heart-filter{
    animation: floatHeart 1.2s linear forwards;
}

.confetti{
    animation: confettiFall 1.2s linear forwards;
}

@keyframes floatHeart{
    0%{
        transform: translateY(0) scale(0.8);
        opacity:1;
    }
    100%{
        transform: translateY(-200px) scale(1.4);
        opacity:0;
    }
}

@keyframes confettiFall{
    0%{
        transform: translateY(0) rotate(0deg);
        opacity:1;
    }
    100%{
        transform: translateY(200px) rotate(360deg);
        opacity:0;
    }
}
.confetti{
    position: fixed;
    pointer-events:none;
    z-index:9999;
    animation: cannonShot 1.5s ease-out forwards;
}

@keyframes cannonShot{
    0%{
        transform: translate(-50%,0) scale(0.5);
        opacity:1;
    }

    70%{
        transform: translate(
            calc(-50% + var(--x)),
            calc(var(--y))
        ) rotate(360deg);
        opacity:1;
    }

    100%{
        transform: translate(
            calc(-50% + var(--x)),
            calc(var(--y) + 200px)
        ) rotate(720deg);
        opacity:0;
    }
}
</style>
<div class="container py-5 d-flex justify-content-center">



    <div class="card shadow-lg border-0" style="max-width: 500px; width:100%;">
        
        <div class="card-body p-4">

            {{-- HEADER STRUK --}}
            <div class="text-center mb-4">
                <h4 class="fw-bold">DHS FINANCE</h4>
                <small class="text-muted">Bukti Konfirmasi Pembayaran</small>
                <hr>
            </div>

            {{-- DETAIL --}}
            <div class="mb-2 d-flex justify-content-between">
                <span>Kode Transaksi</span>
                <strong>{{ $transaksi->kode_transaksi }}</strong>
            </div>

            <div class="mb-2 d-flex justify-content-between">
                <span>Customer</span>
                <strong>{{ $transaksi->nama_customer }}</strong>
            </div>

            <div class="mb-2 d-flex justify-content-between">
                <span>Tanggal Transaksi</span>
                <strong>{{ $transaksi->tanggal->format('d M Y') }}</strong>
            </div>

            <div class="mb-2 d-flex justify-content-between">
                <span>Jatuh Tempo</span>
                <strong>{{ $transaksi->jatuh_tempo?->format('d M Y') ?? '-' }}</strong>
            </div>

            <hr>

            {{-- TOTAL --}}
            <div class="d-flex justify-content-between fs-5 mb-3">
                <span>Total Pembayaran</span>
                <strong class="text-success">
                    Rp {{ number_format($transaksi->total,0,',','.') }}
                </strong>
            </div>

            <hr>

            {{-- STATUS --}}
            <div class="text-center mb-4">
                @if($transaksi->status === 'paid')
                    <span class="badge bg-success px-3 py-2">
                        SUDAH DIBAYAR
                    </span>
                @else
                    <span class="badge bg-danger px-3 py-2">
                        BELUM DIBAYAR
                    </span>
                @endif
            </div>

            {{-- BUTTON --}}
            @if($transaksi->status !== 'paid')
                <form action="{{ route('finance.transaksi.payment.process', $transaksi->id) }}"
                      method="POST">
                    @csrf
                    @method('PATCH')

                    <div class="d-grid">
                        <button type="submit" class="btn btn-success btn-payment btn-lg">
                            <i class="fas fa-check me-1 "></i>
                            Konfirmasi Pembayaran
                        </button>
                    </div>
                </form>
            @endif

            <div class="text-center mt-3">
                <a href="{{ route('finance.transaksi.show', $transaksi->id) }}"
                   class="btn btn-outline-secondary btn-sm">
                    Kembali
                </a>
            </div>

        </div>
    </div>

</div>


<audio id="duitSound">
    <source src="/sounds/wow-duit-nih.mp3" type="audio/mp3">
</audio>
<script>
document.addEventListener("DOMContentLoaded", function(){

document.querySelectorAll('.btn-payment').forEach(function(btn){

btn.addEventListener('click', function(e){

    e.preventDefault();

    const form = this.closest("form");

    // 🔊 SOUND
    const sound = document.getElementById("duitSound");
    sound.currentTime = 0;
    sound.play();

    // ❤️ HEART
    for(let i=0;i<60;i++){

        const heart = document.createElement("div");
        heart.className = "heart-filter";
        heart.innerHTML = "❤️";

        heart.style.left = Math.random()*100 + "vw";
        heart.style.top = "60vh";
        heart.style.fontSize = (20 + Math.random()*40) + "px";

        document.body.appendChild(heart);

        setTimeout(()=>{heart.remove()},1200);
    }

    // 💥 CONFETTI CANNON
    const emojis = ["🌸","✨","❤️","💖","🌹","💫","🌺","🌷"];

    for(let i=0;i<120;i++){

        const confetti = document.createElement("div");
        confetti.className = "confetti";
        confetti.innerHTML = emojis[Math.floor(Math.random()*emojis.length)];

        confetti.style.fontSize = (18 + Math.random()*30) + "px";
        confetti.style.left = "50vw";
        confetti.style.top = "80vh";

        const x = (Math.random()-0.5)*500;
        const y = -(Math.random()*600 + 200);

        confetti.style.setProperty('--x', x + "px");
        confetti.style.setProperty('--y', y + "px");

        document.body.appendChild(confetti);

        setTimeout(()=>{confetti.remove()},1500);
    }

    // submit setelah animasi
    setTimeout(()=>{
        form.submit();
    },1500);

});

});

});
</script>

@endsection