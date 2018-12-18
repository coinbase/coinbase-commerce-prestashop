<style>
	p.payment_module a.coinbase:after {
		display: block;
		content: "\f054";
		position: absolute;
		right: 15px;
		margin-top: -11px;
		top: 50%;
		font-family: "FontAwesome";
		font-size: 25px;
		height: 22px;
		width: 14px;
		color: #777;
	}
</style>

<p class="payment_module">
	<a class="coinbase" href="{$link->getModuleLink('coinbase', 'process', [], true)|escape:'html'}" title="{l s='Pay by Coinbase Commerce' mod='coinbase'}">
		{l s='Pay by Coinbase Commerce' mod='cheque'} <span>{l s='(pay using cryptocurrencies)' mod='coinbase'}</span>
	</a>
</p>
