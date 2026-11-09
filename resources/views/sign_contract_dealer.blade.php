<div class="modal fade" id="contractModal" tabindex="-1" aria-labelledby="contractModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
   <form method='POST' action='{{url('submit-contract-dealer/'.$dealer->id)}}' onsubmit='show()' enctype="multipart/form-data" class="validation-wizard wizard-circle mt-5">
      @csrf
      <input type="hidden" name="partner_record_type" value="{{ $partnerRecordType ?? 'dealer' }}">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="contractModalLabel"><i class="bi bi-file-earmark-text"></i> Contract Signing</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" style="max-height: 75vh; overflow-y: auto;">
          <div class="form-section">
            <label>KASUNDUAN NG GAZ LITE MICRO-ENTREPRENEUR RETAILER</label><br>
            <p class="text-muted">(GAZ LITE MICRO-ENTREPRENEUR RETAILER AGREEMENT)</p>
            <p>
              Ito ay nagpapatunay na ako, <b><u>{{$dealer->name}}</b></u>, ay kusang-loob na pumapayag na maging Gaz Lite Micro-Entrepreneur Retailer ng Pascal Resources Energy Inc.<br>
              Ang kasunduang ito ay nagtatakda ng mga patakaran, responsibilidad, at obligasyon ng Micro-Entrepreneur Retailer upang matiyak ang maayos na distribusyon ng Gaz Lite clean cooking products sa komunidad.
              <br><small class="text-muted">(This certifies that I, <b><u>{{$dealer->name}}</b></u>, voluntarily agree to become a Gaz Lite Micro-Entrepreneur Retailer of Pascal Resources Energy Inc. This agreement establishes the responsibilities and obligations of the Micro-Entrepreneur Retailer in distributing Gaz Lite clean cooking products within the community.)</small>
            </p>
            <label>Misyon ng Pascal Resources Energy, Inc.</label><br>
            <p class="text-muted">(Mission of Pascal Resources Energy, Inc.)</p>
            <p>
              Ang layunin ng Pascal Resources Energy, Inc. ay palawakin ang access sa malinis, ligtas, at abot-kayang enerhiya sa pagluluto para sa mga sambahayan sa pamamagitan ng paggamit ng Gaz Lite cookstoves at refills. Nilalayon din ng programa na mapabuti ang kalusugan ng pamilya, mabawasan ang paggamit ng kahoy at uling, mapangalagaan ang kapaligiran, at makalikha ng mga oportunidad sa kabuhayan sa komunidad.
              <br><small class="text-muted">(The mission of Pascal Resources Energy, Inc. is to expand access to clean, safe, and affordable cooking energy for households through the use of Gaz Lite cookstoves and refills. The program also aims to improve family health, reduce reliance on firewood and charcoal, protect the environment and create livelihood opportunities within communities.)</small>
            </p>
              {{-- <p>
                  Ito ay pagpapatunay na ako, <b><u>{{$dealer->name}}</b></u>, ay pumapayag na makibahagi sa proyekto ng <strong>CARBON CREDIT GAZ LITE FEASIBILITY STUDY</strong>.
              </p> --}}

            {{-- <h6 class="mt-4"><strong>Mga panuntunan na dapat sundin ng mga kalahok sa proyekto:</strong></h6> --}}

            <ol>
              {{-- 1 --}}
              <li>
                Katayuan bilang Independent Sales Agent<br>
                <small>(Independent Sales Agent Status)</small><br><br>
                Ako ay kikilos bilang independent sales agent ng Kumpanya at hindi bilang empleyado o opisyal na kinatawan nito.
                <br><small class="text-muted">(I shall operate as an independent sales agent of the Company and not as an employee or official representative.)</small>
              </li>
              {{-- 2 --}}
              <li class="mt-3">
                Eksklusibong Pagbebenta ng Gaz Lite Products<br>
                <small>(Exclusive Sale of Gaz Lite Products)</small><br><br>
                Sumasang-ayon ako na hindi magbebenta o magtataguyod ng anumang produktong kahalintulad o direktang kakumpitensya ng Gaz Lite clean cooking products habang ako ay kalahok sa programang ito.
                <br><small class="text-muted">(I agree not to sell or promote any products that compete with Gaz Lite clean cooking products while participating in this program.)</small>
              </li>
              {{-- 3 --}}
              <li class="mt-3">
                Mga Responsibilidad ng Retailer<br>
                <small>(Retailer Responsibilities)</small><br><br>
                Sumasang-ayon ako na:<br>
                  •	magtaguyod at magbenta ng Gaz Lite cookstoves at refills sa komunidad<br>
                  •	magbigay ng tamang impormasyon tungkol sa ligtas na paggamit ng produkto<br>
                  •	magpanatili ng talaan ng benta at imbentaryo<br>
                  •	makipagtulungan sa monitoring at reporting ng programa.
                <br><small class="text-muted">(I agree to promote and sell Gaz Lite cookstoves and refills, provide accurate product information, maintain sales and inventory records and cooperate with monitoring and reporting.)</small>
              </li>
              {{-- 4 --}}
              <li class="mt-3">
                Minimum na Antas ng Imbentaryo (50%)<br>
                <small>(Minimum Inventory Stocking Level)</small><br><br>
                Ako ay magpapanatili ng hindi bababa sa limampung porsyento (50%) ng aking karaniwang stocking capacity ng Gaz Lite products sa lahat ng oras.
                <br><small class="text-muted">(I will maintain at least fifty percent (50%) of my normal stocking capacity of Gaz Lite products at all times.)</small>
              </li>
              {{-- 5 --}}
              <li class="mt-3">
                Minimum na Refill Inventory Requirement<br>
                <small>(Minimum Inventory Stocking Level)</small><br><br>
                Ako ay magpapanatili ng minimum na imbentaryo ng Gaz Lite 330g refills na katumbas ng tinatayang dalawang (2) linggong demand.
                <br><small class="text-muted">(I will maintain a minimum inventory of Gaz Lite 330g refills equivalent to approximately two weeks of expected demand.)</small>
              </li>
              {{-- 6 --}}
              <li class="mt-3">
                Prayoridad sa mga Kalahok ng Project RISE<br>
                <small>(Priority Supply for Project RISE Households)</small><br><br>
                Ako ay magbibigay ng priyoridad sa mga rehistradong kalahok ng Project RISE sa pagbili ng Gaz Lite refills lalo na kung limitado ang suplay.
                <br><small class="text-muted">(I agree to give priority access to Gaz Lite refills for registered Project RISE households.)</small>
              </li>
              {{-- 7 --}}
              <li class="mt-3">
                SPARK Digital Monitoring System<br>
                <small>(Priority Supply for Project RISE Households)</small><br><br>
                SPARK (Stove Provision Analytics for Residential Kitchens) ay ang opisyal na digital monitoring at point-of-sale platform na ginagamit upang itala at subaybayan ang distribusyon ng stove, refill transactions at partisipasyon ng mga sambahayan sa ilalim ng Project RISE at Genesis sa loob ng sampung (10) taon na programa.<br>Sumasang-ayon ako na gamitin ang SPARK system at tiyakin na ang lahat ng transaksyon ay tumpak at napapanahong naitatala sa system.
                <br><small class="text-muted">(SPARK (Stove Provision Analytics for Residential Kitchens) is the official digital monitoring and point-of-sale platform used to record stove distribution per unique household, refill transactions and household participation under Project RISE and Genesis for the ten-year program period.)</small>
              </li>
              {{-- 8 --}}
              <li class="mt-3">
                Responsibilidad sa Internet at Komunikasyon<br>
                <small>(Connectivity and Communication Responsibility)</small><br><br>
                Ako ay mananagot sa aking sariling internet connection, mobile data, at communication expenses na kinakailangan upang magamit ang aplikasyon o sistema ng SPARK.
                <br><small class="text-muted">(I am responsible for my own internet connectivity, mobile data and communication expenses required to operate the SPARK application.)</small>
              </li>
              {{-- 9 --}}
              <li class="mt-3">
                Data Privacy<br>
                <small>(Connectivity and Communication Responsibility)</small><br><br>
                Ang lahat ng personal na impormasyon na makokolekta kaugnay ng kasunduang ito ay pangangalagaan alinsunod sa Data Privacy Act of 2012.
                <br><small class="text-muted">(All personal information collected shall be protected in accordance with the Data Privacy Act of 2012.)</small>
              </li>
              {{-- 10 --}}
              <li class="mt-3">
                Paunawa ng Pagtigil at Paglipat ng Retailer<br>
                <small>(Notice of Discontinuance and Transition Support)</small><br><br>
                Kung nais kong ihinto ang aking pakikilahok bilang Micro-Entrepreneur Retailer, ako ay magbibigay ng nakasulat na paunawa na hindi bababa sa tatlumpung (30) araw sa Kumpanya.<br>
                Sa panahong ito, ako ay makikipagtulungan sa Kumpanya upang matiyak ang maayos na paglipat ng operasyon, kabilang ang pagtulong sa paghahanap ng isa pang kwalipikadong Micro-Entrepreneur mula sa komunidad upang ipagpatuloy ang distribusyon ng Gaz Lite products.
                <br><small class="text-muted">(If I intend to discontinue participation, I will provide written notice of at least thirty (30) days to the Company and assist in identifying another qualified Micro-Entrepreneur in the community to ensure continuity of supply.)</small>
              </li>
              {{-- 11 --}}
              <li class="mt-3">
                Pagwawakas ng Kasunduan<br>
                <small>(Termination)</small><br><br>
                Maaaring wakasan ng Kumpanya ang kasunduang ito kung ako ay:<br>
                  •	lumabag sa mga patakaran ng programa<br>
                  •	nagbenta ng competing products<br>
                  •	hindi sumunod sa inventory requirements<br>
                  •	hindi nagtala ng mga transaksyon sa SPARK system.
                <br><small class="text-muted">(The Company may terminate this Agreement if I violate program policies, sell competing products, fail to maintain inventory requirements, or fail to record transactions in SPARK.)</small>
              </li>
            </ol>
          </div>
          <!-- Contract Text -->
          <div class="mb-4">
            <p><strong>Contract Agreement:</strong> By signing below, you acknowledge that you have read and agreed to the terms of the service as described in this document.</p>
          </div>

          <!-- Signature Pad -->
          <div class="mb-3 text-center">
             <a href='{{url("/signature-dealer/".$dealer->id)}}' ><button id="signHereBtn" class="btn btn-success btn-lg" type='button'>
              ✍️ Sign Here
            </button>
             </a>
           
          </div>

          <!-- Hidden input to store signature -->
          <input type="hidden" name="contract_signature" id="contract_signature" />

        </div>
         <div class="modal-footer">
        <button type="button" class="btn bg-danger-subtle text-danger  waves-effect"
          data-bs-dismiss="modal">
          Close
        </button>
      </div>
      </div>
    </form>
  </div>
</div>
