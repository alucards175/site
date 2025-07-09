<template>
  <div class="deposit py-16">
    <div class="container">
      <h1 class="text-4xl font-bold text-center mb-12">Пополнение баланса</h1>
      <div class="max-w-md mx-auto card">
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium mb-2">Сумма</label>
            <input type="number" placeholder="Введите сумму" class="input-field w-full">
          </div>
          <div>
            <label class="block text-sm font-medium mb-2">Способ оплаты</label>
            <select class="input-field w-full">
              <option>Qiwi Wallet</option>
              <option>ЮMoney</option>
              <option>Crypto Bot</option>
              <option>Банковская карта</option>
            </select>
          </div>
          <button class="btn-primary w-full">Пополнить</button>
        </div>
      </div>
    </div>
  </div>
</template>