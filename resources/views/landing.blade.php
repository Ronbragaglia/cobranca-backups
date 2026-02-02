<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CobrançaAuto - Automação de Cobranças via WhatsApp</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        
        body {
            font-family: 'Inter', sans-serif;
        }
        
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .hero-video {
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }
        
        .floating {
            animation: floating 3s ease-in-out infinite;
        }
        
        @keyframes floating {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
            100% { transform: translateY(0px); }
        }
        
        .pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px -12px rgba(0, 0, 0, 0.3);
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <nav class="fixed w-full z-50 bg-white/90 backdrop-blur-md border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <i class="fab fa-whatsapp text-3xl text-green-600 mr-2"></i>
                    <span class="text-2xl font-bold text-gray-900">CobrançaAuto</span>
                </div>
                <div class="hidden md:flex items-center space-x-8">
                    <a href="#features" class="text-gray-700 hover:text-green-600 transition-colors">Funcionalidades</a>
                    <a href="#pricing" class="text-gray-700 hover:text-green-600 transition-colors">Planos</a>
                    <a href="#cases" class="text-gray-700 hover:text-green-600 transition-colors">Cases</a>
                    <a href="#testimonials" class="text-gray-700 hover:text-green-600 transition-colors">Depoimentos</a>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('login') }}" class="text-gray-700 hover:text-green-600 transition-colors">Login</a>
                    <a href="{{ route('register') }}" class="bg-green-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-green-700 transition-colors">
                        Começar Grátis
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="gradient-bg min-h-screen flex items-center pt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <!-- Left: Content -->
                <div class="text-white">
                    <div class="inline-block bg-white/20 backdrop-blur-sm px-4 py-2 rounded-full mb-6">
                        <span class="text-sm font-medium">🚀 Lançamento Oficial</span>
                    </div>
                    
                    <h1 class="text-5xl lg:text-6xl font-extrabold mb-6 leading-tight">
                        Cobrança WhatsApp<br>
                        <span class="text-yellow-300">99% de Entrega</span>
                    </h1>
                    
                    <p class="text-xl text-white/90 mb-8 leading-relaxed">
                        Automatize suas cobranças e receba pagamentos 5x mais rápido. 
                        <span class="font-semibold">Teste grátis 7 dias</span> - sem cartão de crédito.
                    </p>
                    
                    <div class="flex flex-col sm:flex-row gap-4 mb-8">
                        <a href="#pricing" class="bg-white text-green-600 px-8 py-4 rounded-xl font-bold text-lg hover:bg-yellow-300 transition-all transform hover:scale-105 shadow-xl">
                            <i class="fas fa-rocket mr-2"></i>
                            Começar Agora - Grátis
                        </a>
                        <a href="#demo" class="border-2 border-white text-white px-8 py-4 rounded-xl font-semibold text-lg hover:bg-white/10 transition-all">
                            <i class="fas fa-play-circle mr-2"></i>
                            Ver Demo em 60s
                        </a>
                    </div>
                    
                    <!-- Stats -->
                    <div class="grid grid-cols-3 gap-4">
                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 text-center">
                            <div class="text-4xl font-bold mb-1">10k+</div>
                            <div class="text-sm text-white/80">Cobranças/dia</div>
                        </div>
                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 text-center">
                            <div class="text-4xl font-bold mb-1">99%</div>
                            <div class="text-sm text-white/80">Taxa de entrega</div>
                        </div>
                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 text-center">
                            <div class="text-4xl font-bold mb-1">5x</div>
                            <div class="text-sm text-white/80">Mais rápido</div>
                        </div>
                    </div>
                </div>
                
                <!-- Right: Video -->
                <div class="relative">
                    <div class="floating">
                        <div class="hero-video bg-white rounded-3xl overflow-hidden shadow-2xl">
                            <video 
                                id="demo-video" 
                                class="w-full rounded-3xl"
                                poster="https://via.placeholder.com/800x600/667eea/ffffff?text=Demo+Video"
                                controls
                                preload="metadata"
                            >
                                <source src="/videos/demo.mp4" type="video/mp4">
                                <source src="/videos/demo.webm" type="video/webm">
                                Seu navegador não suporta vídeos.
                            </video>
                            
                            <!-- Play Overlay -->
                            <div id="play-overlay" class="absolute inset-0 flex items-center justify-center bg-black/30 cursor-pointer" onclick="playVideo()">
                                <div class="bg-white rounded-full p-6 shadow-xl hover:scale-110 transition-transform">
                                    <i class="fas fa-play text-4xl text-green-600"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Floating Elements -->
                    <div class="absolute -top-10 -right-10 bg-white rounded-2xl p-4 shadow-xl hidden lg:block">
                        <div class="flex items-center space-x-2">
                            <div class="bg-green-100 text-green-600 px-3 py-1 rounded-full text-sm font-semibold">
                                LIVE
                            </div>
                            <span class="text-gray-700 font-medium">Demo Real</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-900 mb-4">
                    Funcionalidades que Transformam seu Negócio
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Tudo o que você precisa para automatizar cobranças e escalar seus resultados
                </p>
            </div>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="card-hover bg-gray-50 rounded-2xl p-8 transition-all duration-300">
                    <div class="bg-green-100 w-16 h-16 rounded-2xl flex items-center justify-center mb-6">
                        <i class="fab fa-whatsapp text-3xl text-green-600"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">Envio Automático</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Envie cobranças via WhatsApp automaticamente em segundos. 
                        99% de taxa de entrega garantida.
                    </p>
                </div>
                
                <!-- Feature 2 -->
                <div class="card-hover bg-gray-50 rounded-2xl p-8 transition-all duration-300">
                    <div class="bg-blue-100 w-16 h-16 rounded-2xl flex items-center justify-center mb-6">
                        <i class="fas fa-file-csv text-3xl text-blue-600"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">Importação em Lote</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Importe centenas de cobranças via CSV em minutos. 
                        Processamento inteligente e validação automática.
                    </p>
                </div>
                
                <!-- Feature 3 -->
                <div class="card-hover bg-gray-50 rounded-2xl p-8 transition-all duration-300">
                    <div class="bg-purple-100 w-16 h-16 rounded-2xl flex items-center justify-center mb-6">
                        <i class="fas fa-chart-line text-3xl text-purple-600"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">Analytics Avançado</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Acompanhe métricas em tempo real. 
                        Relatórios detalhados e insights para tomada de decisão.
                    </p>
                </div>
                
                <!-- Feature 4 -->
                <div class="card-hover bg-gray-50 rounded-2xl p-8 transition-all duration-300">
                    <div class="bg-yellow-100 w-16 h-16 rounded-2xl flex items-center justify-center mb-6">
                        <i class="fas fa-robot text-3xl text-yellow-600"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">Templates Personalizados</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Crie mensagens únicas para sua marca. 
                        Variáveis dinâmicas para personalização total.
                    </p>
                </div>
                
                <!-- Feature 5 -->
                <div class="card-hover bg-gray-50 rounded-2xl p-8 transition-all duration-300">
                    <div class="bg-red-100 w-16 h-16 rounded-2xl flex items-center justify-center mb-6">
                        <i class="fas fa-shield-alt text-3xl text-red-600"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">Segurança Enterprise</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Criptografia de ponta a ponta. 
                        Auditoria completa e compliance LGPD.
                    </p>
                </div>
                
                <!-- Feature 6 -->
                <div class="card-hover bg-gray-50 rounded-2xl p-8 transition-all duration-300">
                    <div class="bg-indigo-100 w-16 h-16 rounded-2xl flex items-center justify-center mb-6">
                        <i class="fas fa-code text-3xl text-indigo-600"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">API Pública</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Integre com seus sistemas facilmente. 
                        Documentação completa e suporte técnico.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section id="pricing" class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-900 mb-4">
                    Planos para Todos os Tamanhos de Negócio
                </h2>
                <p class="text-xl text-gray-600">
                    Comece grátis, escale conforme cresce. Sem compromisso de longo prazo.
                </p>
            </div>
            
            <div class="grid md:grid-cols-3 gap-8 max-w-6xl mx-auto">
                <!-- Basic Plan -->
                <div class="bg-white rounded-3xl p-8 shadow-xl border-2 border-transparent hover:border-green-500 transition-all duration-300">
                    <div class="text-center mb-8">
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">Básico</h3>
                        <div class="flex items-baseline justify-center mb-4">
                            <span class="text-5xl font-extrabold text-gray-900">R$97</span>
                            <span class="text-xl text-gray-600">/mês</span>
                        </div>
                        <div class="bg-green-100 text-green-700 px-4 py-2 rounded-full inline-block font-semibold">
                            Teste Grátis 7 Dias
                        </div>
                    </div>
                    
                    <ul class="space-y-4 mb-8">
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-600 mt-1 mr-3"></i>
                            <span class="text-gray-700">1 instância WhatsApp</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-600 mt-1 mr-3"></i>
                            <span class="text-gray-700">500 mensagens/mês</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-600 mt-1 mr-3"></i>
                            <span class="text-gray-700">Upload CSV ilimitado</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-600 mt-1 mr-3"></i>
                            <span class="text-gray-700">Templates básicos</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-600 mt-1 mr-3"></i>
                            <span class="text-gray-700">Suporte por email</span>
                        </li>
                    </ul>
                    
                    <a href="{{ route('register') }}" class="block w-full bg-green-600 text-white text-center py-4 rounded-xl font-bold text-lg hover:bg-green-700 transition-colors">
                        Começar Teste Grátis
                    </a>
                </div>
                
                <!-- Pro Plan -->
                <div class="bg-gradient-to-br from-blue-600 to-purple-600 rounded-3xl p-8 shadow-2xl transform scale-105 relative">
                    <div class="absolute -top-4 left-1/2 transform -translate-x-1/2">
                        <span class="bg-yellow-400 text-yellow-900 px-6 py-2 rounded-full text-sm font-bold shadow-lg">
                            MAIS POPULAR
                        </span>
                    </div>
                    
                    <div class="text-center mb-8">
                        <h3 class="text-2xl font-bold text-white mb-2">Pro</h3>
                        <div class="flex items-baseline justify-center mb-4">
                            <span class="text-5xl font-extrabold text-white">R$297</span>
                            <span class="text-xl text-white/80">/mês</span>
                        </div>
                        <div class="bg-white/20 text-white px-4 py-2 rounded-full inline-block font-semibold">
                            +200% de Escala
                        </div>
                    </div>
                    
                    <ul class="space-y-4 mb-8 text-white">
                        <li class="flex items-start">
                            <i class="fas fa-check mt-1 mr-3"></i>
                            <span>3 instâncias WhatsApp</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check mt-1 mr-3"></i>
                            <span>5.000 mensagens/mês</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check mt-1 mr-3"></i>
                            <span>Analytics avançado</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check mt-1 mr-3"></i>
                            <span>QR personalizado</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check mt-1 mr-3"></i>
                            <span>Suporte prioritário</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check mt-1 mr-3"></i>
                            <span>Todos os recursos básicos</span>
                        </li>
                    </ul>
                    
                    <a href="{{ route('register') }}" class="block w-full bg-white text-blue-600 text-center py-4 rounded-xl font-bold text-lg hover:bg-gray-100 transition-colors">
                        Começar Teste Grátis
                    </a>
                </div>
                
                <!-- Enterprise Plan -->
                <div class="bg-white rounded-3xl p-8 shadow-xl border-2 border-transparent hover:border-purple-500 transition-all duration-300">
                    <div class="text-center mb-8">
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">Enterprise</h3>
                        <div class="flex items-baseline justify-center mb-4">
                            <span class="text-5xl font-extrabold text-gray-900">R$997</span>
                            <span class="text-xl text-gray-600">/mês</span>
                        </div>
                        <div class="bg-purple-100 text-purple-700 px-4 py-2 rounded-full inline-block font-semibold">
                            Escala Ilimitada
                        </div>
                    </div>
                    
                    <ul class="space-y-4 mb-8">
                        <li class="flex items-start">
                            <i class="fas fa-check text-purple-600 mt-1 mr-3"></i>
                            <span class="text-gray-700">10 instâncias WhatsApp</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-purple-600 mt-1 mr-3"></i>
                            <span class="text-gray-700">Mensagens ilimitadas</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-purple-600 mt-1 mr-3"></i>
                            <span class="text-gray-700">Analytics avançado</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-purple-600 mt-1 mr-3"></i>
                            <span class="text-gray-700">Suporte 24/7 dedicado</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-purple-600 mt-1 mr-3"></i>
                            <span class="text-gray-700">SLA garantido</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-purple-600 mt-1 mr-3"></i>
                            <span class="text-gray-700">Integração customizada</span>
                        </li>
                    </ul>
                    
                    <a href="{{ route('register') }}" class="block w-full bg-purple-600 text-white text-center py-4 rounded-xl font-bold text-lg hover:bg-purple-700 transition-colors">
                        Falar com Vendas
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Cases Section -->
    <section id="cases" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-900 mb-4">
                    Cases de Sucesso - 99% de Entrega
                </h2>
                <p class="text-xl text-gray-600">
                    Veja como empresas transformaram suas cobranças
                </p>
            </div>
            
            <div class="grid md:grid-cols-3 gap-8">
                <!-- Case 1 -->
                <div class="bg-gray-50 rounded-2xl p-8">
                    <div class="flex items-center mb-6">
                        <div class="bg-green-100 w-16 h-16 rounded-full flex items-center justify-center mr-4">
                            <span class="text-2xl font-bold text-green-600">99%</span>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">Contabilidade Silva</h3>
                            <p class="text-gray-600">+300% de recebimentos</p>
                        </div>
                    </div>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        "Reduzimos o tempo de cobrança de 15 dias para 2 horas. 
                        Nossos clientes pagam muito mais rápido agora."
                    </p>
                    <div class="flex items-center text-green-600 font-semibold">
                        <i class="fas fa-chart-line mr-2"></i>
                        +R$50k/mês em recebimentos
                    </div>
                </div>
                
                <!-- Case 2 -->
                <div class="bg-gray-50 rounded-2xl p-8">
                    <div class="flex items-center mb-6">
                        <div class="bg-blue-100 w-16 h-16 rounded-full flex items-center justify-center mr-4">
                            <span class="text-2xl font-bold text-blue-600">98%</span>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">Clínica Saúde+</h3>
                            <p class="text-gray-600">+500% de eficiência</p>
                        </div>
                    </div>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        "Automatizamos todo o processo de cobrança. 
                        Nossa equipe foca apenas em atendimento."
                    </p>
                    <div class="flex items-center text-blue-600 font-semibold">
                        <i class="fas fa-clock mr-2"></i>
                        40h/semana economizadas
                    </div>
                </div>
                
                <!-- Case 3 -->
                <div class="bg-gray-50 rounded-2xl p-8">
                    <div class="flex items-center mb-6">
                        <div class="bg-purple-100 w-16 h-16 rounded-full flex items-center justify-center mr-4">
                            <span class="text-2xl font-bold text-purple-600">99.5%</span>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">Advocacia Costa</h3>
                            <p class="text-gray-600">+200% de conversão</p>
                        </div>
                    </div>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        "A taxa de entrega é impressionante. 
                        Nossos clientes respondem quase sempre."
                    </p>
                    <div class="flex items-center text-purple-600 font-semibold">
                        <i class="fas fa-users mr-2"></i>
                        +150 clientes ativos
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section id="testimonials" class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-900 mb-4">
                    O Que Nossos Clientes Dizem
                </h2>
                <p class="text-xl text-gray-600">
                    Mais de 1.000 empresas já transformaram suas cobranças
                </p>
            </div>
            
            <div class="grid md:grid-cols-3 gap-8">
                <!-- Testimonial 1 -->
                <div class="bg-white rounded-2xl p-8 shadow-lg">
                    <div class="flex items-center mb-6">
                        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mr-4">
                            <span class="text-2xl font-bold text-green-600">RC</span>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-900">Roberto Carvalho</h4>
                            <p class="text-gray-600 text-sm">Contador, São Paulo</p>
                        </div>
                    </div>
                    <p class="text-gray-700 italic mb-4">
                        "Impressionante! Reduzi meu tempo de cobrança de 80%. 
                        Agora consigo focar no atendimento ao cliente."
                    </p>
                    <div class="flex text-yellow-500">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                </div>
                
                <!-- Testimonial 2 -->
                <div class="bg-white rounded-2xl p-8 shadow-lg">
                    <div class="flex items-center mb-6">
                        <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mr-4">
                            <span class="text-2xl font-bold text-blue-600">MA</span>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-900">Maria Alves</h4>
                            <p class="text-gray-600 text-sm">Financeira, Rio de Janeiro</p>
                        </div>
                    </div>
                    <p class="text-gray-700 italic mb-4">
                        "A taxa de entrega de 99% é real! 
                        Meus clientes pagam muito mais rápido agora."
                    </p>
                    <div class="flex text-yellow-500">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                </div>
                
                <!-- Testimonial 3 -->
                <div class="bg-white rounded-2xl p-8 shadow-lg">
                    <div class="flex items-center mb-6">
                        <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mr-4">
                            <span class="text-2xl font-bold text-purple-600">JS</span>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-900">João Santos</h4>
                            <p class="text-gray-600 text-sm">Advogado, Belo Horizonte</p>
                        </div>
                    </div>
                    <p class="text-gray-700 italic mb-4">
                        "O ROI é incrível. Paguei R$97 e recuperei o valor 
                        no primeiro mês de uso."
                    </p>
                    <div class="flex text-yellow-500">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 gradient-bg">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-4xl lg:text-5xl font-extrabold text-white mb-6">
                Pronto para Transformar suas Cobranças?
            </h2>
            <p class="text-xl text-white/90 mb-8 max-w-2xl mx-auto">
                Comece hoje e automatize suas cobranças em menos de 90 segundos. 
                Teste grátis 7 dias - sem cartão de crédito.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('register') }}" class="bg-white text-green-600 px-10 py-5 rounded-xl font-bold text-xl hover:bg-yellow-300 transition-all transform hover:scale-105 shadow-xl">
                    <i class="fas fa-rocket mr-2"></i>
                    Começar Teste Grátis
                </a>
                <a href="https://wa.me/5511999999999" class="border-2 border-white text-white px-10 py-5 rounded-xl font-bold text-xl hover:bg-white/10 transition-all">
                    <i class="fab fa-whatsapp mr-2"></i>
                    Falar com Vendas
                </a>
            </div>
            
            <p class="text-white/70 mt-8 text-sm">
                ✓ 99% de taxa de entrega • ✓ Setup em 90s • ✓ Suporte 24/7
            </p>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-4 gap-8 mb-8">
                <div>
                    <h4 class="text-lg font-bold mb-4">Produto</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#features" class="hover:text-white transition-colors">Funcionalidades</a></li>
                        <li><a href="#pricing" class="hover:text-white transition-colors">Planos</a></li>
                        <li><a href="#cases" class="hover:text-white transition-colors">Cases</a></li>
                        <li><a href="#testimonials" class="hover:text-white transition-colors">Depoimentos</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="text-lg font-bold mb-4">Empresa</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#" class="hover:text-white transition-colors">Sobre Nós</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Blog</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Carreiras</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Contato</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="text-lg font-bold mb-4">Suporte</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#" class="hover:text-white transition-colors">Central de Ajuda</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Documentação</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Status do Sistema</a></li>
                        <li><a href="https://wa.me/5511999999999" class="hover:text-white transition-colors">WhatsApp</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="text-lg font-bold mb-4">Legal</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#" class="hover:text-white transition-colors">Termos de Uso</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Política de Privacidade</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">LGPD</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Cookies</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="border-t border-gray-800 pt-8 flex flex-col md:flex-row justify-between items-center">
                <div class="flex items-center mb-4 md:mb-0">
                    <i class="fab fa-whatsapp text-2xl text-green-500 mr-2"></i>
                    <span class="text-xl font-bold">CobrançaAuto</span>
                </div>
                <div class="text-gray-400 text-sm">
                    © 2026 CobrançaAuto. Todos os direitos reservados.
                </div>
            </div>
        </div>
    </footer>

    <script>
        function playVideo() {
            const video = document.getElementById('demo-video');
            const overlay = document.getElementById('play-overlay');
            
            if (video.paused) {
                video.play();
                overlay.style.display = 'none';
            } else {
                video.pause();
                overlay.style.display = 'flex';
            }
        }
        
        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
    </script>
</body>
</html>
