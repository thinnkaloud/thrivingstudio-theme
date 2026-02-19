(function($){
    wp.customize.bind('ready', function() {
        var settingId = 'thrivingstudio_social_profiles';
        var container = $('#sortable-social-profiles');
        if (!container.length) return;
        var platforms = [
            { value: 'facebook', label: 'Facebook' },
            { value: 'instagram', label: 'Instagram' },
            { value: 'youtube', label: 'YouTube' },
            { value: 'pinterest', label: 'Pinterest' }
        ];
        function render(profiles) {
            container.empty();
            var list = $('<ul class="sortable-social-list"></ul>');
            profiles.forEach(function(profile, idx) {
                var li = $('<li class="sortable-social-item" style="margin-bottom:18px;padding:12px 10px 10px 10px;background:#f8fafc;border-radius:6px;display:flex;flex-direction:column;gap:6px;"></li>');
                var labelRow = $('<div style="font-weight:500;margin-bottom:2px;">Platform</div>');
                var selectRow = $('<div style="display:flex;align-items:center;gap:8px;"></div>');
                var select = $('<select class="platform-select" style="min-width:110px;width:180px;"></select>');
                platforms.forEach(function(p) {
                    select.append('<option value="'+p.value+'"'+(profile.platform===p.value?' selected':'')+'>'+p.label+'</option>');
                });
                selectRow.append(select);
                var urlRow = $('<div style="display:flex;align-items:center;gap:8px;margin-top:2px;"></div>');
                var input = $('<input type="url" class="profile-url" style="flex:1;min-width:0;max-width:300px;" placeholder="Enter profile URL..." />').val(profile.url);
                var remove = $('<button type="button" class="button remove-social" style="flex-shrink:0;width:90px;margin-left:8px;padding:2px 10px;font-size:13px;line-height:1.2;">Remove</button>');
                urlRow.append(input).append(remove);
                li.append(labelRow).append(selectRow).append(urlRow);
                list.append(li);
            });
            container.append(list);
            container.append('<div style="margin-top:12px;"><button type="button" class="button add-social">Add Profile</button></div>');
            list.sortable({
                update: function() { save(); }
            });
        }
        function getProfiles() {
            var val = wp.customize(settingId)();
            try { return JSON.parse(val) || []; } catch(e) { return []; }
        }
        function save() {
            var profiles = [];
            container.find('.sortable-social-item').each(function(){
                var platform = $(this).find('.platform-select').val();
                var url = $(this).find('.profile-url').val();
                if (platform) profiles.push({platform:platform, url:url});
            });
            wp.customize(settingId)(JSON.stringify(profiles));
        }
        container.on('change', '.platform-select, .profile-url', save);
        container.on('click', '.remove-social', function(){
            $(this).closest('li').remove();
            save();
        });
        container.on('click', '.add-social', function(){
            var profiles = getProfiles();
            profiles.push({platform:'facebook', url:''});
            render(profiles);
            save();
        });
        // Initial render
        render(getProfiles());
        // Listen for setting changes
        wp.customize(settingId, function(value){
            value.bind(function(newval){
                render(JSON.parse(newval||'[]'));
            });
        });
    });
})(jQuery); 