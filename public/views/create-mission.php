<div class="msb-create-shipping" id="msb-create-shipping">
  <h2 id="SaisirMissionExempleLive">Exemple live</h2>
  <a href="js/sample/exemple-saisiemissionsimple.js">Télécharger le fichier javascript correspondant</a>
  <p>
    Renseignez vos identifiants de test et cliquez sur se connecter
  </p>
  
  <div class="form" role="form">
    <div class="form-group">
      <p>
        Commande une mission pour la prestion T1 de Montpellier à Lille pour le client
      </p>
    </div>
    
    <button type="button" class="btn btn-success" v-on:click="createShipment">Commander</button>
    <button type="button" class="btn btn-success" v-on:click="shipmentSearch">shipmentSearch</button>
    <button type="button" class="btn btn-success" v-on:click="authentication">authentication</button>

    <img v-if="saving" src="https://dispatchweb.eureka-technology.fr/documentationapiweb/Content/images/ellipsis.gif" height="32px" />
    <div class="form-horizontal" v-if="shipmentResult.Id">
      <div class="form-group">
        <label class="col-sm-2 control-label">Numéro de mission</label>
        <div class="col-sm-10">
          <p class="form-control-static">{{ shipmentResult.Id }}</p>
        </div>
      </div>
      <div class="form-group">
        <label class="col-sm-2 control-label">Distance</label>
        <div class="col-sm-10">
          <p class="form-control-static">{{ shipmentResult.Distance }} km</p>
        </div>
      </div>
      <div class="form-group">
        <label class="col-sm-2 control-label">Montant TTC</label>
        <div class="col-sm-10">
          <p class="form-control-static">{{ shipmentResult.PriceWithTaxes }} €</p>
        </div>
      </div>
    </div>
    <div>
      <h3>
      items
            </h3>
            <pre>
      {{ items }}
      </pre>
      <h3>
            Requête API
            </h3>
            <pre>
      {{ apiRequest }}
      </pre>
      <h3>
            Réponse API
            </h3>
      <pre>
      {{ apiResponse }}
      </pre>
    </div>
  </div>
</div>