{% if announcement %}
<div id="announcement-popup-{{ announcement.announcement_id }}" class="meschain-announcement-popup">
  <div class="meschain-announcement-content" style="background: {{ templates[announcement.template].bg }}; border-left: 6px solid {{ templates[announcement.template].border }};">
    <button type="button" class="meschain-announcement-close" onclick="closeAnnouncement({{ announcement.announcement_id }})">&times;</button>
    
    <h2 class="meschain-announcement-title">
      {% if templates[announcement.template].icon %}{{ templates[announcement.template].icon|raw }} {% endif %}
      {{ announcement.title }}
    </h2>
    
    <div class="meschain-announcement-body">
      {{ announcement.content|raw }}
    </div>
    
    <div class="meschain-announcement-date">
      {{ announcement.date }}
    </div>
    
    {% if announcement.attachments %}
    <div class="meschain-announcement-attachments">
      Ekler: 
      {% for attachment in announcement.attachments %}
      <a href="{{ attachment_url }}{{ attachment }}" target="_blank">📎 {{ attachment }}</a>
      {% endfor %}
    </div>
    {% endif %}
  </div>
</div>

<script type="text/javascript">
function closeAnnouncement(id) {
  document.getElementById('announcement-popup-' + id).style.display = 'none';
  
  $.ajax({
    url: 'index.php?route=extension/module/announcement/markAsViewed&user_token={{ user_token }}',
    type: 'post',
    data: {announcement_id: id},
    dataType: 'json'
  });
}
</script>
{% endif %} 