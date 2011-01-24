{{ include file="html_header.tpl" }}
<script type="text/javascript" src="include/js/fValidate/fValidate.config.js"></script>
<script type="text/javascript" src="include/js/fValidate/fValidate.core.js"></script>
<script type="text/javascript" src="include/js/fValidate/fValidate.lang-enUS.js"></script>
<script type="text/javascript" src="include/js/fValidate/fValidate.validators.js"></script>
<script type="text/javascript" src="js/prototype.js"></script>
<script type="text/javascript" src="js/scriptaculous.js?load=effects,builder"></script>
<script type="text/javascript" src="js/lightbox.js"></script>
<link rel="stylesheet" href="css/lightbox.css" type="text/css" media="screen" />

<form action="index.php" method="post" name="install_form" autocomplete="off">
<tr>
  <td valign="top">
    <table class="header" cellspacing="0" cellpadding="0">
    <tr>
      <td width="70%">
        <div class="title">Sample Site</div>
      </td>
      <td width="30%" nowrap>
        <div class="navigate"><input
        class="nav_button" type="button" value="&#139; Previous"
        onclick="submitForm( install_form, 'mainconfig' );" /> &nbsp;
      {{ if $host_os == 'linux' }}
        <input
        class="nav_button" type="button" value="Next &#155;"
        onclick="if (validateForm(install_form, 0, 1, 0, 1, 8) == true) {
                 submitForm(install_form, 'cronjobs'); }" />
      {{ else }}
        <input
        class="nav_button" type="button" value="Next &#155;"
        onclick="if (validateForm(install_form, 0, 1, 0, 1, 8) == true) {
                 submitForm(install_form, 'finish'); }" />
      {{ /if }}
        </div>
      </td>
    </tr>
    </table>
    <div class="table_spacer"> </div>
    <table class="inside" cellspacing="0" cellpadding="0">
    <tr>
      <td>
        <table width="100%" cellspacing="0" cellpadding="0">
        <tr>
          <th colspan="3" align="left" class="innerHead">
            <div class="subtitle">Load Sample Data:</div>
          </th>
        </tr>
        <tr>
          <td width="35%" valign="top">
            <div class="help">
              <p>Is this the first time you install <em>Newscoop</em>?
              Are not you familiar with the template engine system?
              Do you want to see <em>Newscoop</em> in action before start
              writing template files for your own Web site? Then, choose
              "Yes" to install the sample site and you will can see a
              simple and functional Web site to get familiar with the
              system.</p>

              <p>Otherwise, simply check "No" and click on "Next" button to
              finish the installation.</p>
            </div>
          </td>
          <td width="5%">&nbsp;</td>
          <td valign="top" width="60%" class="template-container">
            <div class="message">{{ $message }}</div>
            <div class="form_field">
              <label for="install_demo">Please choose a demo template:</label> &nbsp;
              <input id="install_demo_no" name="install_demo" value="0" type="radio" {{ if !$dm.loaddemo }} checked {{ /if }}> <label for="install_demo_no">No, thanks!</label>
              {{ foreach from=$sample_templates key="step" item="t" }}
              <input type="radio" id="install_demo" name="install_demo" value="{{ $t }}" style="margin: 0 5px 0 13px" {{ if $dm.loaddemo eq $t }} checked {{ /if }}/><label for="install_demo">{{ $t }}</label>
              {{ /foreach }}
            </div>
            {{ foreach from=$sample_templates key="step" item="t" }}
            <div class="demo_img">
              <a href="sample_templates/{{ $t }}/screenshot_large.jpg" rel="lightbox"><img src="sample_templates/{{ $t }}/screenshot.jpg" rel="lightbox" title="{{ $t }}" /></a>
              <p>{{include file="./../sample_templates/$t/description.txt"}}</p>
            </div>
            {{ /foreach }}
          </td>
        </tr>
        </table>
      </td>
    </tr>
    </table>
  </td>
  <td valign="top" nowrap>
    <table class="right_header" cellspacing="0" cellpadding="0">
    <tr>
      <td>
        <div class="title">Progress...</div>
      </td>
    </tr>
    </table>
    <div class="table_spacer"> </div>
    <table class="right" cellspacing="0" cellpadding="0">
    <tr>
      <td>
        <ul id="steps_list">
        {{ foreach from=$step_titles key="step" item="s" }}
          {{ if $s.order < 5 }}
            <li class="stepdone">{{ $s.title }}</span>
          {{ else }}
            <li>{{ $s.title }}
          {{ /if }}
          {{ if $s.title eq $current_step_title }}
            &nbsp; <img src="img/checked.png" />
          {{ /if }}
          </li>
        {{ /foreach }}
        </ul>
      </td>
    </tr>
    </table>
    <div class="table_spacer"> </div>
    <div align="center">
      <img src="img/installation-progress.png" />
    </div>
  </td>
</tr>
<input type="hidden" name="this_step" value="loaddemo" />
<input type="hidden" name="step" value="" />
</form>
</table>

{{ include file="html_footer.tpl" }}
