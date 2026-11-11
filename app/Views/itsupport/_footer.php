</main>
<?php
$footerText = \App\Models\SettingModel::get('footer_text', 'CodeIgniter 4 port');
$footerUser = session('its_user');
$footerVersion = $footerUser && ($footerUser['role'] ?? '') !== 'client' ? (new \App\Libraries\SystemUpdater())->version() : null;
?>
<footer><?= esc(\App\Models\SettingModel::get('company_name', 'IT Support')) ?><?= $footerText !== '' ? ' - ' . esc($footerText) : '' ?><?php if ($footerVersion) { ?> &middot; v<?= esc($footerVersion) ?><?php if (($footerUser['role'] ?? '') === 'admin') { ?> &middot; <a href="<?= base_url('its-updates#whats-new') ?>" style="color:inherit;">What's new</a><?php } ?><?php } ?></footer>
<script>
(() => {
    const language = document.querySelector('#ui-language');
    const sizeButtons = [...document.querySelectorAll('[data-text-size]')];
    const sizes = {small:0.9, medium:1, large:1.12, xlarge:1.25};
    const copy = {
        fr: {'Knowledge Base':'Base de connaissances',Security:'Sécurité',Health:'Santé du système',Billing:'Facturation',Search:'Rechercher','Help articles':'Articles d\'aide','My Tickets':'Mes tickets','Report an Issue':'Signaler un problème',Work:'Activité',Customers:'Clients','IT Operations':'Opérations IT','Insights & Admin':'Analyses & administration',Resources:'Ressources',Dashboard:'Tableau de bord',Tickets:'Tickets',Quotes:'Devis',Clients:'Clients',Assets:'Équipements',Network:'Réseau',Forms:'Formulaires',Reports:'Rapports',Admin:'Administration',Features:'Fonctionnalités',Help:'Aide',Logout:'Déconnexion',Language:'Langue',Text:'Texte','Dark mode':'Mode sombre','Light mode':'Mode clair'},
        es: {'Knowledge Base':'Base de conocimiento',Security:'Seguridad',Health:'Estado del sistema',Billing:'Facturación',Search:'Buscar','Help articles':'Artículos de ayuda','My Tickets':'Mis tickets','Report an Issue':'Reportar un problema',Work:'Trabajo',Customers:'Clientes','IT Operations':'Operaciones de TI','Insights & Admin':'Análisis y administración',Resources:'Recursos',Dashboard:'Panel',Tickets:'Tickets',Quotes:'Presupuestos',Clients:'Clientes',Assets:'Activos',Network:'Red',Forms:'Formularios',Reports:'Informes',Admin:'Administración',Features:'Funciones',Help:'Ayuda',Logout:'Cerrar sesión',Language:'Idioma',Text:'Texto','Dark mode':'Modo oscuro','Light mode':'Modo claro'},
        sw: {'Knowledge Base':'Maktaba ya maarifa',Security:'Usalama',Health:'Afya ya mfumo',Billing:'Ankara',Search:'Tafuta','Help articles':'Makala za usaidizi','My Tickets':'Tiketi zangu','Report an Issue':'Ripoti tatizo',Work:'Kazi',Customers:'Wateja','IT Operations':'Uendeshaji wa TEHAMA','Insights & Admin':'Ripoti na usimamizi',Resources:'Rasilimali',Dashboard:'Dashibodi',Tickets:'Tiketi',Quotes:'Nukuu',Clients:'Wateja',Assets:'Vifaa',Network:'Mtandao',Forms:'Fomu',Reports:'Ripoti',Admin:'Usimamizi',Features:'Vipengele',Help:'Msaada',Logout:'Ondoka',Language:'Lugha',Text:'Maandishi','Dark mode':'Hali nyeusi','Light mode':'Hali angavu'},
        ar: {'Knowledge Base':'قاعدة المعرفة',Security:'الأمان',Health:'صحة النظام',Billing:'الفوترة',Search:'بحث','Help articles':'مقالات المساعدة','My Tickets':'تذاكري','Report an Issue':'الإبلاغ عن مشكلة',Work:'العمل',Customers:'العملاء','IT Operations':'عمليات تقنية المعلومات','Insights & Admin':'الرؤى والإدارة',Resources:'الموارد',Dashboard:'لوحة التحكم',Tickets:'التذاكر',Quotes:'عروض الأسعار',Clients:'العملاء',Assets:'الأصول',Network:'الشبكة',Forms:'النماذج',Reports:'التقارير',Admin:'الإدارة',Features:'الميزات',Help:'المساعدة',Logout:'تسجيل الخروج',Language:'اللغة',Text:'النص','Dark mode':'الوضع الداكن','Light mode':'الوضع الفاتح'},
        zh: {'Knowledge Base':'知识库',Security:'安全',Health:'系统健康',Billing:'计费',Search:'搜索','Help articles':'帮助文章','My Tickets':'我的工单','Report an Issue':'报告问题',Work:'工作',Customers:'客户','IT Operations':'IT 运维','Insights & Admin':'分析与管理',Resources:'资源',Dashboard:'仪表板',Tickets:'工单',Quotes:'报价',Clients:'客户',Assets:'资产',Network:'网络',Forms:'表单',Reports:'报告',Admin:'管理',Features:'功能',Help:'帮助',Logout:'退出',Language:'语言',Text:'文字','Dark mode':'深色模式','Light mode':'浅色模式'}
    };
    const pageCopy = {"fr":{"Board":"Tableau","Ticket board":"Tableau des tickets","Workflow":"Flux de travail","New":"Nouveau","In progress":"En cours","Waiting parts":"En attente de pièces","Waiting approval":"En attente d’approbation","Completed":"Terminé","Closed":"Fermé","System updates":"Mises à jour du système","Check for updates":"Rechercher des mises à jour","Component":"Composant","Installed":"Installé","Latest":"Dernière","Status":"Statut","Update details":"Détails de la mise à jour","Server environment":"Environnement du serveur","Update source":"Source des mises à jour","System integrity":"Intégrité du système","Backups on the server":"Sauvegardes sur le serveur","Upload an update package":"Téléverser un paquet de mise à jour","Automatic checking":"Vérification automatique","Update log":"Journal des mises à jour","Clear log":"Effacer le journal","Save":"Enregistrer","Create backup now":"Créer une sauvegarde","Users":"Utilisateurs","Branding & Settings":"Marque et paramètres","Templates":"Modèles","Mail":"Courriel","Mail Log":"Journal des courriels","Maintenance":"Maintenance","Audit":"Audit","Changes":"Modifications","Updates":"Mises à jour","Backup":"Sauvegarde","Add User":"Ajouter un utilisateur","Name":"Nom","Username":"Nom d’utilisateur","Role":"Rôle","Password":"Mot de passe","My tickets":"Mes tickets","Ticket":"Ticket","Subject":"Sujet","Priority":"Priorité","Opened":"Ouvert le","Report an issue":"Signaler un problème","Open":"Ouverts","Resolved":"Résolus","Total":"Total","Send":"Envoyer","Description":"Description","Login":"Connexion","Updated":"Mis à jour","Conversation":"Conversation","Reply":"Répondre","Software":"Logiciel","Not checked":"Non vérifié","Up to date":"À jour","Healthy":"Sain","Problems found":"Problèmes détectés"},"es":{"Board":"Tablero","Ticket board":"Tablero de tickets","Workflow":"Flujo de trabajo","New":"Nuevo","In progress":"En curso","Waiting parts":"Esperando piezas","Waiting approval":"Esperando aprobación","Completed":"Completado","Closed":"Cerrado","System updates":"Actualizaciones del sistema","Check for updates":"Buscar actualizaciones","Component":"Componente","Installed":"Instalado","Latest":"Última","Status":"Estado","Update details":"Detalles de la actualización","Server environment":"Entorno del servidor","Update source":"Origen de actualizaciones","System integrity":"Integridad del sistema","Backups on the server":"Copias de seguridad en el servidor","Upload an update package":"Subir un paquete de actualización","Automatic checking":"Comprobación automática","Update log":"Registro de actualizaciones","Clear log":"Borrar registro","Save":"Guardar","Create backup now":"Crear copia ahora","Users":"Usuarios","Branding & Settings":"Marca y ajustes","Templates":"Plantillas","Mail":"Correo","Mail Log":"Registro de correo","Maintenance":"Mantenimiento","Audit":"Auditoría","Changes":"Cambios","Updates":"Actualizaciones","Backup":"Copia de seguridad","Add User":"Agregar usuario","Name":"Nombre","Username":"Usuario","Role":"Rol","Password":"Contraseña","My tickets":"Mis tickets","Ticket":"Ticket","Subject":"Asunto","Priority":"Prioridad","Opened":"Abierto","Report an issue":"Informar un problema","Open":"Abiertos","Resolved":"Resueltos","Total":"Total","Send":"Enviar","Description":"Descripción","Login":"Iniciar sesión","Updated":"Actualizado","Conversation":"Conversación","Reply":"Responder","Software":"Software","Not checked":"Sin comprobar","Up to date":"Al día","Healthy":"Saludable","Problems found":"Problemas encontrados"},"sw":{"Board":"Ubao","Ticket board":"Ubao wa tiketi","Workflow":"Mtiririko wa kazi","New":"Mpya","In progress":"Inaendelea","Waiting parts":"Inasubiri vipuri","Waiting approval":"Inasubiri idhini","Completed":"Imekamilika","Closed":"Imefungwa","System updates":"Masasisho ya mfumo","Check for updates":"Angalia masasisho","Component":"Kipengele","Installed":"Imesakinishwa","Latest":"Ya karibuni","Status":"Hali","Update details":"Maelezo ya sasisho","Server environment":"Mazingira ya seva","Update source":"Chanzo cha masasisho","System integrity":"Uadilifu wa mfumo","Backups on the server":"Nakala rudufu kwenye seva","Upload an update package":"Pakia kifurushi cha sasisho","Automatic checking":"Ukaguzi wa kiotomatiki","Update log":"Kumbukumbu ya masasisho","Clear log":"Futa kumbukumbu","Save":"Hifadhi","Create backup now":"Unda nakala rudufu sasa","Users":"Watumiaji","Branding & Settings":"Chapa na mipangilio","Templates":"Violezo","Mail":"Barua pepe","Mail Log":"Kumbukumbu ya barua pepe","Maintenance":"Matengenezo","Audit":"Ukaguzi","Changes":"Mabadiliko","Updates":"Masasisho","Backup":"Nakala rudufu","Add User":"Ongeza mtumiaji","Name":"Jina","Username":"Jina la mtumiaji","Role":"Jukumu","Password":"Nenosiri","My tickets":"Tiketi zangu","Ticket":"Tiketi","Subject":"Mada","Priority":"Kipaumbele","Opened":"Imefunguliwa","Report an issue":"Ripoti tatizo","Open":"Zilizo wazi","Resolved":"Zilizotatuliwa","Total":"Jumla","Send":"Tuma","Description":"Maelezo","Login":"Ingia","Updated":"Imesasishwa","Conversation":"Mazungumzo","Reply":"Jibu","Software":"Programu","Not checked":"Haijakaguliwa","Up to date":"Imesasishwa kikamilifu","Healthy":"Salama","Problems found":"Matatizo yamepatikana"},"ar":{"Board":"اللوحة","Ticket board":"لوحة التذاكر","Workflow":"سير العمل","New":"جديد","In progress":"قيد التنفيذ","Waiting parts":"بانتظار قطع الغيار","Waiting approval":"بانتظار الموافقة","Completed":"مكتمل","Closed":"مغلق","System updates":"تحديثات النظام","Check for updates":"التحقق من التحديثات","Component":"المكوّن","Installed":"المثبّت","Latest":"الأحدث","Status":"الحالة","Update details":"تفاصيل التحديث","Server environment":"بيئة الخادم","Update source":"مصدر التحديثات","System integrity":"سلامة النظام","Backups on the server":"النسخ الاحتياطية على الخادم","Upload an update package":"رفع حزمة تحديث","Automatic checking":"التحقق التلقائي","Update log":"سجل التحديثات","Clear log":"مسح السجل","Save":"حفظ","Create backup now":"إنشاء نسخة احتياطية الآن","Users":"المستخدمون","Branding & Settings":"العلامة والإعدادات","Templates":"القوالب","Mail":"البريد","Mail Log":"سجل البريد","Maintenance":"الصيانة","Audit":"التدقيق","Changes":"التغييرات","Updates":"التحديثات","Backup":"نسخ احتياطي","Add User":"إضافة مستخدم","Name":"الاسم","Username":"اسم المستخدم","Role":"الدور","Password":"كلمة المرور","My tickets":"تذاكري","Ticket":"التذكرة","Subject":"الموضوع","Priority":"الأولوية","Opened":"تاريخ الفتح","Report an issue":"الإبلاغ عن مشكلة","Open":"مفتوحة","Resolved":"تم حلها","Total":"الإجمالي","Send":"إرسال","Description":"الوصف","Login":"تسجيل الدخول","Updated":"تم التحديث","Conversation":"المحادثة","Reply":"رد","Software":"البرمجيات","Not checked":"لم يتم التحقق","Up to date":"محدّث","Healthy":"سليم","Problems found":"تم العثور على مشاكل"},"zh":{"Board":"看板","Ticket board":"工单看板","Workflow":"工作流","New":"新建","In progress":"进行中","Waiting parts":"等待配件","Waiting approval":"等待批准","Completed":"已完成","Closed":"已关闭","System updates":"系统更新","Check for updates":"检查更新","Component":"组件","Installed":"已安装","Latest":"最新","Status":"状态","Update details":"更新详情","Server environment":"服务器环境","Update source":"更新来源","System integrity":"系统完整性","Backups on the server":"服务器上的备份","Upload an update package":"上传更新包","Automatic checking":"自动检查","Update log":"更新日志","Clear log":"清除日志","Save":"保存","Create backup now":"立即创建备份","Users":"用户","Branding & Settings":"品牌与设置","Templates":"模板","Mail":"邮件","Mail Log":"邮件日志","Maintenance":"维护","Audit":"审计","Changes":"变更","Updates":"更新","Backup":"备份","Add User":"添加用户","Name":"姓名","Username":"用户名","Role":"角色","Password":"密码","My tickets":"我的工单","Ticket":"工单","Subject":"主题","Priority":"优先级","Opened":"开启时间","Report an issue":"报告问题","Open":"未解决","Resolved":"已解决","Total":"总计","Send":"发送","Description":"描述","Login":"登录","Updated":"已更新","Conversation":"对话","Reply":"回复","Software":"软件","Not checked":"未检查","Up to date":"已是最新","Healthy":"正常","Problems found":"发现问题"}};
    const pageSelector = 'main h2, main h3, main th, main label, main button, main summary, main .eyebrow, main .badge, main a.button, main [role=tab], nav a';
    const translate = (code) => {
        const dict = Object.assign({}, copy[code] || {}, pageCopy[code] || {});
        document.querySelectorAll(pageSelector).forEach((element) => {
            if (element.children.length || element.hasAttribute('data-i18n')) return;
            if (!element.dataset.englishText) element.dataset.englishText = element.textContent.trim();
            element.textContent = dict[element.dataset.englishText] || element.dataset.englishText;
        });
        document.documentElement.lang = code;
        document.documentElement.dir = code === 'ar' ? 'rtl' : 'ltr';
        document.querySelectorAll('[data-i18n]').forEach((element) => {
            const key = element.dataset.i18n;
            if (!element.dataset.englishText) element.dataset.englishText = element.textContent.trim();
            element.textContent = dict[key] || element.dataset.englishText;
        });
        document.querySelectorAll('.muted').forEach((element) => {
            if (!element.dataset.englishText) element.dataset.englishText = element.textContent.trim();
            element.textContent = dict[element.dataset.englishText] || element.dataset.englishText;
        });
        document.querySelectorAll('[data-mode-toggle]').forEach((button) => {
            const label = document.documentElement.dataset.mode === 'dark' ? 'Light mode' : 'Dark mode';
            button.textContent = dict[label] || label;
        });
        try { localStorage.setItem('itsupport-language', code); } catch (error) { console.error('Could not save language preference.', error); }
    };
    try {
        const savedLanguage = localStorage.getItem('itsupport-language') || 'en';
        if (language) {
            language.value = savedLanguage;
            language.addEventListener('change', () => translate(language.value));
        }
        translate(savedLanguage);
        const savedSize = localStorage.getItem('itsupport-text-size') || 'medium';
        document.documentElement.style.fontSize = `${(sizes[savedSize] || sizes.medium) * 100}%`;
        sizeButtons.forEach((button) => {
            button.setAttribute('aria-pressed', button.dataset.textSize === savedSize ? 'true' : 'false');
            button.addEventListener('click', () => {
                const size = button.dataset.textSize;
                document.documentElement.style.fontSize = `${(sizes[size] || sizes.medium) * 100}%`;
                sizeButtons.forEach((item) => item.setAttribute('aria-pressed', item === button ? 'true' : 'false'));
                localStorage.setItem('itsupport-text-size', size);
            });
        });
    } catch (error) {
        console.error('Could not restore display preferences.', error);
    }

    const toggle = document.querySelector('[data-mode-toggle]');
    const root = document.documentElement;
    const setMode = (dark) => {
        root.dataset.mode = dark ? 'dark' : 'light';
        if (!dark) root.removeAttribute('data-mode');
        if (toggle) {
            toggle.setAttribute('aria-pressed', dark ? 'true' : 'false');
            toggle.setAttribute('aria-label', dark ? 'Switch to light mode' : 'Switch to dark mode');
            const selectedLanguage = language?.value || 'en';
            const modeLabel = dark ? 'Light mode' : 'Dark mode';
            toggle.textContent = copy[selectedLanguage]?.[modeLabel] || modeLabel;
        }
        try { localStorage.setItem('itsupport-mode', dark ? 'dark' : 'light'); } catch (error) { console.error('Could not save appearance preference.', error); }
    };
    if (toggle) {
        const initialDark = root.dataset.mode === 'dark';
        toggle.setAttribute('aria-pressed', initialDark ? 'true' : 'false');
        toggle.setAttribute('aria-label', initialDark ? 'Switch to light mode' : 'Switch to dark mode');
        const modeLabel = initialDark ? 'Light mode' : 'Dark mode';
        toggle.textContent = copy[language?.value || 'en']?.[modeLabel] || modeLabel;
        toggle.addEventListener('click', () => setMode(root.dataset.mode !== 'dark'));
    }
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('<?= esc(base_url('service-worker.js')) ?>')
            .catch((error) => console.error('Service worker registration failed.', error));
    }
})();
</script>
<script>
(() => {
    const groups = () => document.querySelectorAll('nav details.nav-group');
    document.addEventListener('click', e => groups().forEach(d => { if (d.open && !d.contains(e.target)) d.open = false; }));
    document.addEventListener('keydown', e => { if (e.key === 'Escape') groups().forEach(d => { d.open = false; }); });
    groups().forEach(d => d.addEventListener('toggle', () => { if (d.open) groups().forEach(o => { if (o !== d) o.open = false; }); }));
})();
</script>
<?php if (session('its_user') && (session('its_user')['role'] ?? '') !== 'client') { ?>
<style>
#cmd-dialog{border:none;padding:0;background:transparent;width:100%;max-width:100%;height:100%;max-height:100%;overflow:hidden}
#cmd-dialog::backdrop{background:rgba(15,23,42,.55);backdrop-filter:blur(4px)}
.cmd-wrap{position:fixed;top:8vh;left:50%;transform:translateX(-50%);width:min(580px,92vw);background:var(--paper);border:1px solid var(--line);border-radius:18px;box-shadow:0 28px 64px rgba(15,23,42,.22);display:flex;flex-direction:column;max-height:76vh;overflow:hidden}
.cmd-head{display:flex;align-items:center;gap:10px;padding:14px 18px;border-bottom:1px solid var(--line);flex-shrink:0}
.cmd-head-icon{font-size:18px;color:var(--muted);flex-shrink:0}
#cmd-input{flex:1;border:none;background:transparent;font-size:16px;color:var(--ink);outline:none;padding:0;min-width:0}
#cmd-input::placeholder{color:var(--muted)}
.cmd-esc{font:11px/1 monospace;border:1px solid var(--line);border-radius:5px;padding:2px 7px;color:var(--muted);flex-shrink:0}
.cmd-spin{display:none;width:16px;height:16px;border:2px solid var(--line);border-top-color:var(--brand);border-radius:50%;animation:cspin .6s linear infinite;flex-shrink:0}
@keyframes cspin{to{transform:rotate(360deg)}}
#cmd-body{flex:1;overflow-y:auto;overscroll-behavior:contain}
.cmd-sec-lbl{padding:10px 18px 4px;font-size:11px;font-weight:700;letter-spacing:.08em;color:var(--muted);text-transform:uppercase}
.cmd-item{display:flex;align-items:center;gap:12px;padding:9px 18px;text-decoration:none;color:var(--ink);font-size:13.5px;width:100%;border:none;background:none;cursor:pointer;transition:background .1s}
.cmd-item:hover,.cmd-item[aria-selected=true]{background:var(--brand-soft)}
.cmd-item[aria-selected=true] .cmd-ico{color:var(--brand)}
.cmd-ico{width:34px;height:34px;border-radius:9px;background:var(--canvas);display:grid;place-items:center;font-size:15px;flex-shrink:0;transition:background .1s}
.cmd-item:hover .cmd-ico,.cmd-item[aria-selected=true] .cmd-ico{background:color-mix(in srgb,var(--brand) 12%,var(--canvas))}
.cmd-body{flex:1;min-width:0}
.cmd-lbl{white-space:nowrap;overflow:hidden;text-overflow:ellipsis;display:block}
.cmd-sub{font-size:12px;color:var(--muted);display:block}
.cmd-badge{font-size:11px;padding:2px 8px;border-radius:99px;background:var(--brand-soft);color:var(--brand);font-weight:600;white-space:nowrap;flex-shrink:0}
.cmd-empty{padding:36px 18px;text-align:center;color:var(--muted);font-size:13px}
.cmd-empty-icon{font-size:28px;opacity:.3;display:block;margin-bottom:8px}
.cmd-foot{display:flex;gap:16px;padding:10px 18px;border-top:1px solid var(--line);font-size:12px;color:var(--muted);flex-shrink:0}
.cmd-foot kbd{font:11px/1 monospace;border:1px solid var(--line);border-radius:4px;padding:1px 5px;margin-right:3px}
</style>
<dialog id="cmd-dialog" aria-label="Search" aria-modal="true">
<div class="cmd-wrap" role="combobox" aria-expanded="true" aria-owns="cmd-listbox" aria-haspopup="listbox">
<div class="cmd-head">
  <i class="bi bi-search cmd-head-icon" aria-hidden="true"></i>
  <input id="cmd-input" type="search" autocomplete="off" spellcheck="false" placeholder="Search tickets, clients, assets, extensions…" aria-label="Search" aria-autocomplete="list" aria-controls="cmd-listbox">
  <div class="cmd-spin" id="cmd-spin" aria-hidden="true"></div>
  <kbd class="cmd-esc">Esc</kbd>
</div>
<div id="cmd-body">
  <div id="cmd-quick">
    <div class="cmd-sec-lbl">Quick navigation</div>
    <?php
    $cmdNav = [
        ['its-dashboard',        'house',           'Dashboard'],
        ['its-tickets',          'ticket-detailed', 'Tickets'],
        ['its-tickets#new-ticket','plus-circle',    'New ticket'],
        ['its-clients',          'building',        'Clients'],
        ['its-assets',           'hdd',             'Assets'],
        ['its-network',          'wifi',            'Network'],
        ['its-extensions',       'plugin',          'Extensions'],
        ['its-signins',          'person-lock',     'Sign-ins'],
        ['its-updates',          'arrow-up-circle', 'Updates'],
    ];
    $cmdIconColor = ['plus-circle' => '#16a34a', 'arrow-up-circle' => '#d97706', 'person-lock' => '#7c3aed', 'plugin' => '#0891b2'];
    foreach ($cmdNav as [$url, $icon, $label]) { ?>
    <a class="cmd-item" href="<?= base_url($url) ?>" tabindex="-1" aria-selected="false">
      <span class="cmd-ico" style="<?= isset($cmdIconColor[$icon]) ? 'color:' . $cmdIconColor[$icon] : '' ?>"><i class="bi bi-<?= $icon ?>" aria-hidden="true"></i></span>
      <span class="cmd-body"><span class="cmd-lbl"><?= esc($label) ?></span></span>
    </a>
    <?php } ?>
  </div>
  <div id="cmd-listbox" role="listbox" aria-label="Search results" hidden></div>
</div>
<div class="cmd-foot"><span><kbd>↑</kbd><kbd>↓</kbd> Navigate</span><span><kbd>↵</kbd> Open</span><span><kbd>Esc</kbd> Close</span></div>
</div>
</dialog>
<script>
(function(){
var dlg=document.getElementById('cmd-dialog');
if(!dlg||!dlg.showModal)return;
var inp=document.getElementById('cmd-input'),res=document.getElementById('cmd-listbox'),quick=document.getElementById('cmd-quick'),spin=document.getElementById('cmd-spin'),tmr;
var ICONS={ticket:'ticket-detailed',client:'building',asset:'hdd',article:'journal-text',extension:'plugin'};
var COLORS={ticket:'var(--brand)',client:'#16a34a',asset:'#d97706',article:'#7c3aed',extension:'#0891b2'};
function allItems(){return[...document.querySelectorAll('#cmd-dialog .cmd-item')].filter(function(el){return!el.closest('[hidden]')})}
function selectIdx(idx){var items=allItems();items.forEach(function(i){i.setAttribute('aria-selected','false')});if(items[idx]){items[idx].setAttribute('aria-selected','true');items[idx].scrollIntoView({block:'nearest'})}}
function open(){dlg.showModal();inp.value='';res.hidden=true;res.innerHTML='';quick.hidden=false;spin.style.display='';inp.focus()}
document.querySelectorAll('#cmd-open,[data-cmd-open]').forEach(function(b){b.addEventListener('click',open)});
document.addEventListener('keydown',function(e){
  if((e.ctrlKey||e.metaKey)&&e.key.toLowerCase()==='k'){e.preventDefault();open();return}
  if(!dlg.open)return;
  if(e.key==='Escape'){dlg.close();return}
  var items=allItems(),cur=items.findIndex(function(i){return i.getAttribute('aria-selected')==='true'});
  if(e.key==='ArrowDown'){e.preventDefault();selectIdx(cur<items.length-1?cur+1:0)}
  else if(e.key==='ArrowUp'){e.preventDefault();selectIdx(cur<=0?items.length-1:cur-1)}
  else if(e.key==='Enter'){var sel=document.querySelector('#cmd-dialog .cmd-item[aria-selected=true]');if(sel){sel.click();dlg.close()}}
});
dlg.addEventListener('click',function(e){if(e.target===dlg)dlg.close()});
function mkItem(x){
  var a=document.createElement('a');a.className='cmd-item';a.href=x.url;a.setAttribute('aria-selected','false');a.setAttribute('role','option');
  a.addEventListener('click',function(){dlg.close()});
  var ic=document.createElement('span');ic.className='cmd-ico';ic.style.color=COLORS[x.type]||'var(--brand)';ic.innerHTML='<i class="bi bi-'+(ICONS[x.type]||'circle')+'" aria-hidden="true"></i>';
  var bd=document.createElement('span');bd.className='cmd-body';
  var lb=document.createElement('span');lb.className='cmd-lbl';lb.textContent=x.label;bd.appendChild(lb);
  if(x.sub){var sb=document.createElement('span');sb.className='cmd-sub';sb.textContent=x.sub;bd.appendChild(sb)}
  var bg=document.createElement('span');bg.className='cmd-badge';bg.textContent=x.type.charAt(0).toUpperCase()+x.type.slice(1);
  a.appendChild(ic);a.appendChild(bd);a.appendChild(bg);
  return a;
}
inp.addEventListener('input',function(){
  clearTimeout(tmr);
  var q=inp.value.trim();
  if(q.length<2){res.hidden=true;res.innerHTML='';quick.hidden=false;spin.style.display='';return}
  spin.style.display='block';
  tmr=setTimeout(function(){
    fetch('<?= base_url('its-search') ?>?q='+encodeURIComponent(q),{credentials:'same-origin'})
    .then(function(r){return r.json()})
    .then(function(rows){
      spin.style.display='';res.innerHTML='';quick.hidden=true;res.hidden=false;
      if(!rows.length){var em=document.createElement('div');em.className='cmd-empty';em.innerHTML='<i class="bi bi-search cmd-empty-icon"></i>No results for <strong>'+q.replace(/</g,'&lt;')+'</strong>';res.appendChild(em);return}
      var grp={};rows.forEach(function(x){(grp[x.type]=grp[x.type]||[]).push(x)});
      Object.keys(grp).forEach(function(type){
        var sec=document.createElement('div');
        var lbl=document.createElement('div');lbl.className='cmd-sec-lbl';lbl.textContent=type.charAt(0).toUpperCase()+type.slice(1)+'s';
        sec.appendChild(lbl);grp[type].forEach(function(x){sec.appendChild(mkItem(x))});res.appendChild(sec);
      });
    })
    .catch(function(){spin.style.display=''});
  },200);
});
})();
</script>
<?php } ?>
<script>
(function(){
var CTRL='INPUT,SELECT,TEXTAREA';
function isCtrl(e){return e&&CTRL.indexOf(e.tagName)>-1&&e.type!=='hidden'&&e.type!=='checkbox'&&e.type!=='radio'&&e.type!=='submit'}
document.querySelectorAll('main form[method="post" i]').forEach(function(f){
 if(f.dataset.plain!==undefined||f.closest('.seg')||/login|install|2fa|verify/.test(f.getAttribute('action')||'x'))return;
 var kids=[].slice.call(f.children),pairs=0;
 kids.forEach(function(k,i){if(k.tagName==='LABEL'&&isCtrl(kids[i+1]))pairs++});
 if(pairs<3)return;
 var out=[],i=0;
 while(i<kids.length){var k=kids[i],n=kids[i+1];
  if(k.tagName==='LABEL'&&isCtrl(n)&&!(k.querySelector('input'))){
   var w=document.createElement('div');w.className='field'+(n.tagName==='TEXTAREA'||n.type==='file'||/detail|note|desc|message|body|reply|address/i.test(n.name||n.id||'')?' wide':'');
   f.insertBefore(w,k);w.appendChild(k);w.appendChild(n);
   if(n.tagName==='TEXTAREA'&&n.maxLength>0&&n.maxLength<20000){var c=document.createElement('div');c.className='char-count';w.appendChild(c);var up=function(t,cc){return function(){cc.textContent=t.value.length+' / '+t.maxLength}}(n,c);n.addEventListener('input',up);up()}
   i+=2;
  } else i++;
 }
 f.classList.add('form-grid');
 var b=f.querySelector(':scope>button:not([type=button]),:scope>button.btn');
 if(b){var a=document.createElement('div');a.className='form-actions';f.appendChild(a);a.appendChild(b)}
});
document.querySelectorAll('textarea').forEach(function(t){var r=function(){t.style.height='auto';t.style.height=Math.min(t.scrollHeight+2,420)+'px'};t.addEventListener('input',r);r()});
document.querySelectorAll('main form[method="post" i]').forEach(function(f){f.addEventListener('submit',function(e){
 if(e.defaultPrevented)return;var b=e.submitter||f.querySelector('button:not([type=button])');
 if(b&&!b.hasAttribute('data-busy')){setTimeout(function(){b.setAttribute('data-busy','1');b.dataset.t=b.textContent;b.textContent='Saving…'},0);setTimeout(function(){b.removeAttribute('data-busy');if(b.dataset.t)b.textContent=b.dataset.t},8000)}
})});
})();
</script>
</body></html>