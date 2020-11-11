export class TypeRocketConditions {

    loadConditions(obj) {
        let $ = this.$,
            $conditions = $(obj).find('[data-tr-conditions]'),
            that = this;

        $conditions.each((index, item) => {

            if($(item).parents('.tr-repeater-group-template').first().length) { return; }

            let $section = $(item),
                json = $section.attr('data-tr-conditions'),
                conditions = JSON.parse(json),
                name = $section.attr('data-tr-context') || '.',
                context = name.substr(0, name.lastIndexOf('.'));

            $section.on('condition', function() {that.validate($(this), conditions, context, obj)});

            // add watchers
            for(let c in conditions) {
                let rule = conditions[c],
                    accessor = this.getAccessor(context, rule.field),
                    $field = this.getField(accessor, obj);

                $field.addClass('tr-has-conditionals');
                that.bindConditions($field, $section);
            }

            $section.trigger('condition');
        });
    }

    validate($section, conditions, context, obj) {
        // add watchers
        let statement = [];

        for(let c in conditions) {

            let rule = conditions[c],
                valid = false,
                accessor = this.getAccessor(context, rule.field),
                $field = this.getField(accessor, obj),
                value = this.getValue($field, accessor),
                operator = rule['operator'],
                condition = rule['condition'];

            if(operator === '=') {
                if(rule['value'] === true) {
                    if(value) {
                        valid = true;
                    }
                } else if(value == rule['value']) {
                    valid = true;
                }
            } else if(operator === '!=' || operator === '!') {
                if(value != rule['value']) {
                    valid = true;
                }
            } else if(operator === '>') {
                if(value > rule['value']) {
                    valid = true;
                }
            } else if(operator === '<') {
                if(value < rule['value']) {
                    valid = true;
                }
            } else if(operator === 'includes' || operator === 'contains') {
                if(value && value.toLowerCase().includes(rule['value'].toLowerCase())) {
                    valid = true;
                }
            } else if(typeof window[operator] === "function") {
                if(window[operator](value, rule['value'])) {
                    valid = true;
                }
            }

            if(condition && ( condition === 'and' || condition.indexOf('&') !== -1 ) ) {
                statement.push('&&');
            } else if(condition) {
                statement.push('||');
            }

            statement.push(valid ? '1' : '0');
        }

        if(eval(statement.join(' '))) {
            $section.addClass('tr-show-conditional');
        } else {
            $section.removeClass('tr-show-conditional');
        }
    }

    getValue($field, dots) {

        if(!$field.length) {
            console.error('Field not found:', dots);
            return null;
        }

        let type = $field.attr('type')?.toLowerCase(),
            tagName = $field.prop('tagName').toLowerCase(),
            val = false;

        if(type === 'checkbox' || type === 'radio') {
            val = $field.filter(':checked').val() || val;
        } else if(tagName === 'select') {
            val = $field.find(':selected').val() || val;
        } else {
            val = $field.val() || val;
        }

        return val
    }

    getAccessor(context, name) {
        let accessor = context ? context + '.' + name : name;

        if(name[0] === '/' || name[0] === '\\') {

            let root = context.substr(0, context.indexOf('.')) || context;

            return root + '.' + name.substr(1);
        }

        if(name[0] === '<') {
            let depth = name.lastIndexOf('<') + 1;
            context = context.split('.');
            accessor = context.slice(0, context.length - depth).join('.') + '.' + name.substr(depth);

            if(accessor[0] === '.') {
                accessor = accessor.substr(1);
            }
        }

        return accessor;
    }

    getField(accessor, scope = null) {
        let field = this.$(`[data-tr-field="${accessor}"]`);

        if(field.length > 0) {
            return field;
        }

        return this.$(scope).find(`[data-tr-field="${accessor}"]`);
    }

    bindConditions($field, name) {
        let conditionalsBond = $field.data('tr-conditions-bond') ? $field.data('tr-conditions-bond') : [];
        conditionalsBond.push(name);
        $field.data('tr-conditions-bond', conditionalsBond);

        return $field;
    }

    constructor(name, jQuery) {
        this.name = name;
        this.$ = jQuery;

        this.loadConditions(document);
        TypeRocket.repeaterCallbacks.push(this.loadConditions.bind(this));
    }

}