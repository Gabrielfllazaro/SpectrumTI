function getIdTrilha() {
    const params = new URLSearchParams(window.location.search);
    return params.get("id");
}

async function carregarDetalhe() {
    const id = getIdTrilha();

    const res = await fetch(`../backend/trilha_controller.php?action=detalhe&id=${id}`);
    const data = await res.json();

    document.getElementById("trilha-nome").innerText = data.trilha.nome;
    document.getElementById("trilha-desc").innerText = data.trilha.descricao;

    const lista = document.getElementById("lista-aulas");

    if (data.aulas.length === 0) {
        lista.innerHTML = "<p>Nenhuma aula cadastrada</p>";
        return;
    }

    lista.innerHTML = data.aulas.map((aula, index) => `
        <div class="aula-item">
            <div class="aula-info">
                <span class="aula-numero">${index + 1}</span>
                <span>${aula.nome}</span>
            </div>
            <span>🔒</span>
        </div>
    `).join('');
}

document.addEventListener("DOMContentLoaded", () => {
    carregarDetalhe();

    document.getElementById("btn-iniciar").addEventListener("click", () => {
        alert("Aqui depois vai iniciar a trilha");
    });
});