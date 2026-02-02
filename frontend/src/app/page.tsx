export default function Home() {
  return (
    <main className="flex min-h-screen flex-col items-center justify-between p-24">
      <div className="z-10 max-w-5xl w-full items-center justify-between font-mono text-sm lg:flex">
        <p className="fixed left-0 top-0 flex w-full justify-center border-b border-gray-300 bg-gradient-to-b from-zinc-200 pb-6 pt-8 backdrop-blur-2xl dark:border-neutral-800 dark:bg-zinc-800/30 dark:from-inherit lg:static lg:w-auto  lg:rounded-xl lg:border lg:bg-gray-200 lg:p-4 lg:dark:bg-zinc-800/30">
          Sistema de Cobrança
        </p>
      </div>

      <div className="relative flex place-items-center">
        <h1 className="text-4xl font-bold text-center">
          Bem-vindo ao Sistema de Cobrança PS5
        </h1>
      </div>

      <div className="mb-32 grid text-center lg:max-w-5xl lg:w-full lg:mb-0 lg:grid-cols-4 lg:text-left">
        <a
          href="#"
          onClick={() => { if (typeof window !== 'undefined' && window.dataLayer) window.dataLayer.push({ 'event': 'link_click', 'link_name': 'Dashboard' }); }}
          className="group rounded-lg border border-transparent px-5 py-4 transition-colors hover:border-gray-300 hover:bg-gray-100 hover:dark:border-neutral-700 hover:dark:bg-neutral-800/30"
        >
          <h2 className="mb-3 text-2xl font-semibold">
            Dashboard{' '}
            <span className="inline-block transition-transform group-hover:translate-x-1 motion-reduce:transform-none">
              ->
            </span>
          </h2>
          <p className="m-0 max-w-[30ch] text-sm opacity-50">
            Acesse o painel de controle para gerenciar suas cobranças.
          </p>
        </a>

        <a
          href="https://buy.stripe.com/test_1234567890?utm_source=website&utm_medium=link&utm_campaign=landing"
          onClick={() => { if (typeof window !== 'undefined' && window.dataLayer) window.dataLayer.push({ 'event': 'link_click', 'link_name': 'Stripe Payment' }); }}
          className="group rounded-lg border border-transparent px-5 py-4 transition-colors hover:border-gray-300 hover:bg-gray-100 hover:dark:border-neutral-700 hover:dark:bg-neutral-800/30"
        >
          <h2 className="mb-3 text-2xl font-semibold">
            API{' '}
            <span className="inline-block transition-transform group-hover:translate-x-1 motion-reduce:transform-none">
              ->
            </span>
          </h2>
          <p className="m-0 max-w-[30ch] text-sm opacity-50">
            Integre com nossa API para automação.
          </p>
        </a>

        <a
          href="https://wa.me/5511999999999?text=Olá, preciso de suporte&utm_source=website&utm_medium=link&utm_campaign=landing"
          onClick={() => { if (typeof window !== 'undefined' && window.dataLayer) window.dataLayer.push({ 'event': 'link_click', 'link_name': 'Suporte WhatsApp' }); }}
          className="group rounded-lg border border-transparent px-5 py-4 transition-colors hover:border-gray-300 hover:bg-gray-100 hover:dark:border-neutral-700 hover:dark:bg-neutral-800/30"
        >
          <h2 className="mb-3 text-2xl font-semibold">
            Suporte{' '}
            <span className="inline-block transition-transform group-hover:translate-x-1 motion-reduce:transform-none">
              ->
            </span>
          </h2>
          <p className="m-0 max-w-[30ch] text-sm opacity-50">
            Entre em contato para suporte.
          </p>
        </a>

        <a
          href="#"
          onClick={() => { if (typeof window !== 'undefined' && window.dataLayer) window.dataLayer.push({ 'event': 'link_click', 'link_name': 'Documentação' }); }}
          className="group rounded-lg border border-transparent px-5 py-4 transition-colors hover:border-gray-300 hover:bg-gray-100 hover:dark:border-neutral-700 hover:dark:bg-neutral-800/30"
        >
          <h2 className="mb-3 text-2xl font-semibold">
            Documentação{' '}
            <span className="inline-block transition-transform group-hover:translate-x-1 motion-reduce:transform-none">
              ->
            </span>
          </h2>
          <p className="m-0 max-w-[30ch] text-sm opacity-50">
            Leia a documentação completa.
          </p>
        </a>
      </div>

      <div className="mb-32">
        <h2 className="text-2xl font-bold text-center mb-4">Entre em Contato</h2>
        <form onSubmit={(e) => { e.preventDefault(); if (typeof window !== 'undefined' && window.dataLayer) window.dataLayer.push({ 'event': 'form_submit', 'form_name': 'contact' }); alert('Mensagem enviada!'); }} className="flex flex-col items-center space-y-4">
          <input type="email" name="email" placeholder="Seu email" required className="px-4 py-2 border rounded" />
          <textarea name="message" placeholder="Mensagem" required className="px-4 py-2 border rounded w-80 h-24"></textarea>
          <button type="submit" className="px-6 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">Enviar</button>
        </form>
      </div>
    </main>
  )
}