<?php

?>
<!doctype html>
<html lang="ru">
<head>
	<meta charset="utf-8">
	<title>vuetemp</title>
	<meta name="description" content="Журнал событий promocodes">
	<meta name="author" content="SitePoint">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

	<!-- Add this to <head> -->

	<!-- Load required Bootstrap and BootstrapVue CSS -->
	<link type="text/css" rel="stylesheet" href="//unpkg.com/bootstrap/dist/css/bootstrap.min.css" />
	<link type="text/css" rel="stylesheet" href="//unpkg.com/bootstrap-vue@latest/dist/bootstrap-vue.min.css" />

	<!-- Load polyfills to support older browsers -->
	<script src="//polyfill.io/v3/polyfill.min.js?features=es2015%2CIntersectionObserver" crossorigin="anonymous"></script>

	<!-- Load Vue followed by BootstrapVue -->
	<script src="//unpkg.com/vue@latest/dist/vue.min.js"></script>
	<script src="//unpkg.com/bootstrap-vue@latest/dist/bootstrap-vue.min.js"></script>

	<script src="https://cdn.jsdelivr.net/npm/axios@0.19.0/dist/axios.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/lodash@4.17.15/lodash.min.js"></script>

	<script src="https://unpkg.com/vue-spinners-css"></script>
	
	<style>	
		#spinner {
			position: absolute;
		}
		.cellTextCenter {
			text-align: center;
		}		
	</style>	

</head>
<body>
	<div id="info"></div>
	
	<!--Use a component somewhere in your app-->
	<div id="spinner">
		<span v-if="show">
			<spinner-loader color="green" />
		</span>
	</div>
	
	<div id="ctrlForm">{{ message }} <br>
		<input type="checkbox" v-model="cb.checked" v-bind:id="cb.id" v-on:click="realTimeUpdate"> - обновлять лог автоматически &nbsp;
		<span v-if="!cb.checked"><button v-on:click="throttledLogUpdate">Обновить лог</button> </span><br>
		<input type="checkbox" v-model="cb2.checked" v-bind:id="cb2.id" v-on:click="showOnlyErr"> - показывать только ошибки &nbsp;
	</div>
	<hr>

	<div id="bstab">
	<template>
	  <div>
		<b-table striped hover  bordered responsive :items="items" primary-key="eventId"></b-table>
	  </div>
	</template>
	</div>
	
	<hr>
	<div class="cellTextCenter">temp &copy; 2019</div>
	<br>

<script>

var t = (s) => console.log(s);

var spinner = new Vue({ 
	el: '#spinner', 
	data: { 
		show: true,
		tmId: false
	},
	methods: {
		delayShow: function ( msec ) {
			this.tmId = setTimeout( () => this.show = true, msec );
		},
		hide: function () {
			this.show = false;
			clearTimeout( this.tmId );
		}
	} 
})
var bstab = new Vue({
    el: '#bstab',
	data: { items: [] }
})

var app = new Vue({
  el: '#ctrlForm',
  data: {
	message: 'Подключаемся к серверу...',
	updInt: false,
	cb: {
		id: 'cbUpd',
		checked: false
	},
	cb2: {
		id: 'cbShowErr',
		checked: false
	},
	options: {
		updInt: false,
		idLog: 1,
		ping: false,
		showErr: 0,
		clear: false
	}
  },
  created: function () {
	t( 'created' );
    this.throttledLogUpdate = _.throttle( this.logUpdate, 3000 );
  },
  mounted: function () {
	t( 'mounted' );
	this.realTimeUpdate();	
  },  
  methods: {
	showOnlyErr: function() {
		t( 'showOnlyErr' );
		this.options.showErr = +!this.cb2.checked;
		this.options.clear = true;
		spinner.show = true;
		this.throttledLogUpdate();
	},  
	logUpdate: function () {
		if ( this.options.ping ) return;
		this.options.ping = true;

		//t( 'logUpdate' );
		if ( this.options.clear ) {
			this.options.idLog = 1;
			bstab.items = [];
			this.options.clear = false;
		}

		spinner.delayShow( 2500 );
		axios({
			url: 'https://temp.ru/vuetest/data.php',
			params: { id: this.options.idLog, err: this.options.showErr }
		}).then( response => {  
				var data = response.data;
				var r = data.note.data;
				if ( r[0] ) {
					this.options.idLog = r[0][ 'eventId' ];
					bstab.items.splice.apply( bstab.items, [0, 0].concat( r ) );
					bstab.items.splice( 300, bstab.items.length );
				}

				app.message = 'Данные обновлены: ' + data.note.time + ', индекс подгрузки данных: ' + this.options.idLog;
				spinner.hide();
				this.options.ping = false;
		}).catch( error => { 
				t( error ); 
				this.options.ping = false;
		}); 		
	},
	realTimeUpdate: function () {
		t( 'realTimeUpdate' );
		if ( this.cb.checked == false ) {
			t( 'start realTimeUpdate' );
			this.options.updInt = setInterval( this.throttledLogUpdate, 1000 );
			this.cb.checked = true;
		}	
		else {
			t( 'stop realTimeUpdate' );
			clearInterval( this.options.updInt )
			this.cb.checked = false;
		}
    }
  }
})

</script>	
</body>
</html>