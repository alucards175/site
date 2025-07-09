<template>
  <div class="withdraw py-16">
    <div class="container">
      <h1 class="text-4xl font-bold text-center mb-12">Вывод средств</h1>
      <div class="max-w-md mx-auto card">
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium mb-2">Сумма вывода</label>
            <input type="number" placeholder="Введите сумму" class="input-field w-full">
          </div>
          <div>
            <label class="block text-sm font-medium mb-2">Способ вывода</label>
            <select class="input-field w-full">
              <option>Steam Trade</option>
              <option>Qiwi Wallet</option>
              <option>ЮMoney</option>
              <option>Криптовалюта</option>
            </select>
          </div>
          <button class="btn-primary w-full">Вывести</button>
        </div>
      </div>
    </div>
  </div>
</template>