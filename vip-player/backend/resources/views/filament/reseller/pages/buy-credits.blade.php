<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">
            Buy credits with card (Stripe)
        </x-slot>

        <x-slot name="description">
            Credits are used to activate and renew customer devices. Payment is processed securely by Stripe.
            Test mode: use card number 4242 4242 4242 4242, any future expiry and any CVC.
        </x-slot>

        <form wire:submit="checkout" class="space-y-6">
            {{ $this->form }}

            <x-filament::button type="submit" icon="heroicon-o-credit-card">
                Pay with Stripe
            </x-filament::button>
        </form>
    </x-filament::section>
</x-filament-panels::page>
