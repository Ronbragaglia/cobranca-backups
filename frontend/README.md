# Cobrança Frontend

Landing page para o sistema de cobrança.

## Deploy no Vercel

1. Faça o push do código para um repositório Git (ex: GitHub).
2. Conecte o repositório ao Vercel.
3. Configure o domínio personalizado:
   - No painel do Vercel, vá para Settings > Domains.
   - Adicione o domínio ps5cobranca.com (ou similar).
   - Siga as instruções para configurar o DNS (geralmente apontar CNAME para cname.vercel-dns.com).
4. O deploy será automático a cada push.

## Configuração

- NEXT_PUBLIC_BACKEND_URL: URL da API backend (configurado em .env.local)

## Analytics

Adicione Google Tag Manager e Hotjar conforme necessário.