<?php 
session_start();
// Se não houver sessão ativa, redireciona para o index.html comum
if (!isset($_SESSION['cliente_id'])) {
    header("Location: index.html");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Daniele França | Nutricionista</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="index_logado.css">
    <link rel="icon" href="../fotos/images-removebg-preview copy.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        /* Estilo para o campo de CEP aparecer de forma suave */
        #container_cep {
            transition: all 0.3s ease;
        }
        .input-bloqueado {
            background-color: #f0f0f0 !important;
            cursor: not-allowed;
            color: #666 !important;
        }
    </style>
</head>
<body>
    <div class="background-overlay">
        <span class="bg-icon icon-1">🍎</span>
        <span class="bg-icon icon-2">🥗</span>
        <span class="bg-icon icon-3">🍃</span>
        <span class="bg-icon icon-4">🥑</span>
    </div>
<nav class="menu">
    <div class="menu-centro">
        <div class="menu-wrapper">
        <a href="#home">Home</a>
        <a href="#sobre">Sobre mim </a>
        <a href="#agenda">Agendamentos</a>
        <a href="#avalia">Avaliações</a>
        <a href="#rede">Redes sociais</a>
        
    <div class="menu-direita">
        <a href="index.php" class="btn-menu-padrao">
            <i class="fas fa-user-circle"></i> 
            Olá <?= htmlspecialchars($_SESSION['cliente_nome']) ?>, veja seu perfil
        </a>
        <a href="logout_index.php" class="btn-menu-padrao">
            <i class="fas fa-sign-out-alt"></i> 
            Sair
        </a>
    </div>
     </div>
    </div>
</nav>

<section id="home" class="secao home-hero">
    <div class="conteudo">
        <div class="hero-container">
            <div class="hero-texto">
                <span class="hero-badge">
                    Nutricionista • Atendimento Humanizado
                </span>
                <h1 class="hero-titulo">
                    Bem-vindo(a), <?= htmlspecialchars($_SESSION['cliente_nome']) ?>!
                </h1>
                <p>
                    Nutrição personalizada, humanizada e baseada em evidências
                    para transformar sua saúde com equilíbrio e qualidade de vida.
                </p>
                <div class="hero-botoes">
                    <a href="#agenda" class="btn-hero">Agendar Consulta</a>
                    <a href="#sobre" class="btn-secundario">Saiba mais</a>
                </div>
            </div>
            <div class="hero-imagem">
                <div class="card-foto">
                    <img src="../fotos/Captura de tela 2026-01-06 183843.png" alt="Nutricionista Daniele França">
                </div>
            </div>
        </div>
    </div>
</section>

<section id="sobre" class="secao">
    <div class="conteudo">
        <div class="sobre-card">
            <div class="sobre-container">
                <div class="sobre-texto">
                    <p>Oi, sou</p>
                    <span class="cargo">Nutricionista</span>
                    <h2>Daniele França</h2>
                    <p>
                        <strong>Nutricionista por paixão e amor à profissão.</strong>
                        A palavra equilíbrio sempre me acompanha e levo em conta as
                        individualidades de cada paciente.
                    </p>
                </div>
                <div class="sobre-imagem">
                    <div class="card-foto">
                        <img src="../fotos/Captura de tela 2026-01-06 183843.png" alt="Nutricionista Daniele Oliveira">
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="agenda" class="secao">
    <div class="conteudo centralizado">
        <h2>Agendamento</h2>
        <p>Olá <?= htmlspecialchars($_SESSION['cliente_nome']) ?>, reserve seu horário abaixo.</p>
        <div class="form-container">
            <form class="form-estilizado" action="../agendamento/agendar.php" method="POST">
                <input type="text" name="nome" value="<?= htmlspecialchars($_SESSION['cliente_nome']) ?>" readonly required>
                
                <div class="input-grupo">
                    <input type="date" name="data_agendamento" required>
                    <input type="time" name="horario" required>
                </div>

                <select name="nome_servico" required>
                    <option value="" disabled selected hidden>Selecione o serviço</option>
                    <option value="Emagrecimento">Emagrecimento</option>
                    <option value="Reeducação alimentar">Reeducação alimentar</option>
                    <option value="Tratamento nutricional">Tratamento nutricional</option>
                    <option value="Qualidade de vida">Qualidade de vida</option>
                </select>

                <select name="tipo_atendimento" id="tipo_atendimento" onchange="gerenciarCEP()" required>
                    <option value="" disabled selected hidden>Onde será o atendimento?</option>
                    <option value="Online">Atendimento Online</option>
                    <option value="Clinica">Presencial</option>
                    <option value="Domiciliar">Domiciliar</option>
                </select>

                    <div id="container_cep" style="display: none; margin-bottom: 15px;">
            <input type="text" name="cep" id="input_cep" placeholder="CEP" maxlength="9" 
                onblur="buscarEndereco()" class="input-pill">
            
            <input type="text" name="rua" id="rua" placeholder="Rua / Logradouro" readonly 
                class="input-bloqueado" style="width: 100%; margin-top: 8px; padding: 12px; border-radius: 8px;">
            
            <input type="text" name="bairro" id="bairro" placeholder="Bairro" readonly 
                class="input-bloqueado" style="width: 100%; margin-top: 8px; padding: 12px; border-radius: 8px;">
            
            <input type="text" name="cidade" id="cidade" placeholder="Cidade" readonly 
                class="input-bloqueado" style="width: 100%; margin-top: 8px; padding: 12px; border-radius: 8px;">
        </div>
                <input type="tel" name="telefone" placeholder="Telefone" required maxlength="11" 
       value="<?= htmlspecialchars($_SESSION['cliente_telefone'] ?? '') ?>"
       oninput="this.value = this.value.replace(/[^0-9]/g, '');">

<div class="input-grupo">
    <input type="number" name="idade" placeholder="Sua idade" required min="0" max="120"
           value="<?= htmlspecialchars($_SESSION['cliente_idade'] ?? '') ?>">
    
    <select name="genero" required>
    <option value="" disabled <?= !isset($_SESSION['cliente_genero']) ? 'selected' : '' ?> hidden>Gênero</option>
    
    <option value="Masculino" <?= (isset($_SESSION['cliente_genero']) && $_SESSION['cliente_genero'] === 'Masculino') ? 'selected' : '' ?>>Masculino</option>
    
    <option value="Feminino" <?= (isset($_SESSION['cliente_genero']) && $_SESSION['cliente_genero'] === 'Feminino') ? 'selected' : '' ?>>Feminino</option>
    
    <option value="Outro" <?= (isset($_SESSION['cliente_genero']) && $_SESSION['cliente_genero'] === 'Outro') ? 'selected' : '' ?>>Outro</option>
</select>
</div>

                <button type="submit">Confirmar Agendamento</button>
            </form>
        </div>
    </div>
</section>

<section id="avalia" class="secao">
    <div class="conteudo centralizado">
        <h2>Avaliações</h2>
        <p>Olá <?= htmlspecialchars($_SESSION['cliente_nome']) ?>, deixe sua opinião sobre o atendimento ou sistema.</p>
        <div class="form-container">
            <form class="form-estilizado" action="../avaliações/confima.php" method="POST">
                <input type="text" name="nome" value="<?= htmlspecialchars($_SESSION['cliente_nome']) ?>" readonly required>
                <textarea name="comentario" placeholder="Escreva sua avaliação" required></textarea>
                <select name="nota" required>
                    <option value="" disabled selected hidden>Selecione a nota</option>
                    <option value="5">5 - Excelente</option>
                    <option value="4">4 - Muito Bom</option>
                    <option value="3">3 - Bom</option>
                    <option value="2">2 - Regular</option>
                    <option value="1">1 - Ruim</option>
                </select>
                <button type="submit">Enviar avaliação</button>
            </form>
        </div>
    </div>
</section>


 <section id="rede" class="secao">
    <div class="conteudo centralizado">
        <h2>
            Para mais informações entre em contato através das redes sociais
        </h2>
        <p class="social-descricao">
            Quer saber mais sobre como transformar sua saúde? <br>
            Acompanhe dicas diárias no meu Instagram.
            Estou à disposição para te ajudar nessa jornada!
        </p>
        <div class="redes-container">
            <div class="social-wrapper">
                <a href="https://www.instagram.com/danielefranca.nutrioficial/?igsh=MTd6OTdkYjdrdG5mdw%3D%3D#"
                target="_blank"
                class="social-icon">
                    <i class="fab fa-instagram"></i>
                </a>
                <p>@danielefranca.nutrioficial</p>
            </div>
        </div>
    </div>
</section>

<footer class="footer">
    <p>&copy; 2026 Daniele França. Todos os direitos reservados.</p>
    <p class="creditos">Sistema desenvolvido por Ryan Carlos</p>
</footer>

<script>
// ENDEREÇO DA CLÍNICA (Configure aqui os dados da nutricionista)
const enderecoClinica = {
    cep: "12442150", 
    rua: "Rua Antonio Alves Diniz, 37",
    bairro: "Residencial Vista Alegre",
    cidade: "Pindamonhangaba"
};
s
function gerenciarCEP() {
    const local = document.getElementById('tipo_atendimento').value;
    const container = document.getElementById('container_cep');
    const inputCep = document.getElementById('input_cep');
    const inputRua = document.getElementById('rua');
    const inputBairro = document.getElementById('bairro');
    const inputCidade = document.getElementById('cidade');

    if (local === 'Clinica' || local === 'Domiciliar') {
        container.style.display = 'block';
    } else {
        container.style.display = 'none';
        return;
    }

    if (local === 'Clinica') {
        // Trava com os dados da clínica
        inputCep.value = enderecoClinica.cep;
        inputRua.value = enderecoClinica.rua;
        inputBairro.value = enderecoClinica.bairro;
        inputCidade.value = enderecoClinica.cidade;
        
        inputCep.readOnly = true;
        inputCep.classList.add('input-bloqueado');
    } else {
        // Libera e preenche o CEP que já está na sessão
        inputCep.readOnly = false;
        inputCep.classList.remove('input-bloqueado');
        inputCep.value = "<?= $_SESSION['cliente_cep'] ?? '' ?>";
        
        if(inputCep.value !== "") buscarEndereco();
    }
}

// FUNÇÃO QUE CHAMA A BRASILAPI (JSON)
function buscarEndereco() {
    let cep = document.getElementById('input_cep').value.replace(/\D/g, '');

    if (cep.length === 8) {
        fetch(`https://brasilapi.com.br/api/cep/v2/${cep}`)
            .then(response => {
                if (!response.ok) throw new Error();
                return response.json();
            })
            .then(dados => {
                // A BrasilAPI usa: street (rua), neighborhood (bairro) e city (cidade)
                document.getElementById('rua').value = dados.street || "Não encontrado";
                document.getElementById('bairro').value = dados.neighborhood || "Não encontrado";
                document.getElementById('cidade').value = dados.city || "";
            })
            .catch(() => {
                console.warn("Erro ao buscar CEP na BrasilAPI");
            });
    }
}

// Garante que a lógica funcione ao carregar a página
window.onload = gerenciarCEP;
</script>

</body>
</html>