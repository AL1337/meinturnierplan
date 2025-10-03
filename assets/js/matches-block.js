(function(wp) {
  const { registerBlockType } = wp.blocks;
  const { createElement: el, Fragment, useState, useEffect } = wp.element;
  const { SelectControl, Placeholder, Spinner } = wp.components;
  const { __ } = wp.i18n;
  const { apiFetch } = wp;

  registerBlockType('meinturnierplan/matches', {
    title: __('Tournament Matches', 'meinturnierplan-wp'),
    icon: 'calendar-alt',
    category: 'widgets',
    description: __('Display tournament matches from your custom post types.', 'meinturnierplan-wp'),

    attributes: {
      matchesId: {
        type: 'string',
        default: ''
      },
      matchesName: {
        type: 'string',
        default: ''
      }
    },

    edit: function(props) {
      const { attributes, setAttributes } = props;
      const { matchesId, matchesName } = attributes;
      const [matches, setMatches] = useState([]);
      const [loading, setLoading] = useState(true);

      // Fetch matches on component mount
      useEffect(() => {
        const formData = new FormData();
        formData.append('action', 'mtp_get_matches');
        formData.append('nonce', mtpMatchesBlock.nonce);

        fetch(mtpMatchesBlock.ajaxUrl, {
          method: 'POST',
          body: formData
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            setMatches(data.data);
          }
          setLoading(false);
        })
        .catch(error => {
          console.error('Error fetching matches:', error);
          setLoading(false);
        });
      }, []);

      const onChangeMatches = function(value) {
        const selectedMatches = matches.find(match => match.value === value);
        setAttributes({
          matchesId: value,
          matchesName: selectedMatches ? selectedMatches.label : ''
        });
      };

      if (loading) {
        return el(
          Placeholder,
          {
            icon: 'calendar-alt',
            label: __('Tournament Matches', 'meinturnierplan-wp')
          },
          el(Spinner)
        );
      }

      return el(
        Fragment,
        null,
        el(
          Placeholder,
          {
            icon: 'calendar-alt',
            label: __('Tournament Matches', 'meinturnierplan-wp'),
            instructions: matchesId
              ? __('Tournament matches selected: ', 'meinturnierplan-wp') + matchesName
              : __('Choose tournament matches to display.', 'meinturnierplan-wp')
          },
          el(SelectControl, {
            label: __('Select Tournament Matches', 'meinturnierplan-wp'),
            value: matchesId,
            options: matches,
            onChange: onChangeMatches
          })
        )
      );
    },

    save: function() {
      // Return null since we use dynamic rendering
      return null;
    }
  });

})(window.wp);
