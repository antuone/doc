from bs4 import BeautifulSoup

TEMPLATE_INDEX = open('template/index.html').read()
TEMPLATE_LI = open('template/li.html').read()
TEMPLATE_METHOD = open('template/method.html').read()
TEMPLATE_OBJECT = open('template/object.html').read()
TEMPLATE_PARAMETER = open('template/parameter.html').read()
TEMPLATE_TRTDTDTD = open('template/trtdtdtd.html').read()
TEMPLATE_TRTDTD = open('template/trtdtd.html').read()
TEMPLATE_PARAMETER_TABLE = open('template/parameter_table.html').read()
TEMPLATE_RETURN_TABLE = open('template/return_table.html').read()

SOUP = BeautifulSoup(open('structure.xml').read(), 'lxml')
SOUP = SOUP.find('project')

LIS = ''
CONTENT = ''
METHODS = ''
OBJECTS = ''

def getProperty(tag):
    p = TEMPLATE_PARAMETER.replace('{{parameter_type}}', tag.find('type').text.replace('\\',''), 1)
    p = p.replace('{{parameter_name}}', tag.find('name').text, 1)
    return p.replace('{{parameter_description}}', tag.find('description').text, 1)

def getMethod(tag):
    _name = tag.find('name').text
    m = TEMPLATE_METHOD.replace('{{method_name}}', _name)
    if _name == '__construct':
        m = m.replace('{{return}}', 'void', 1)
    else:
        m = m.replace('{{return}}', tag.find('tag', {"name":"return"}).find('type').text, 1)
    m = m.replace('{{method_description}}', tag.find('description').text, 1)
    param = ''
    param_tables = ''
    return_tables = ''
    for tag in tag.find('docblock').contents:
        trtdtdtd = ''
        trtdtd = ''
        try:
            if tag.name == 'tag' and tag['name'] == 'param':
                param += tag['type'] + ' ' + tag['variable'] + ','
                trtdtdtd = TEMPLATE_TRTDTDTD.replace('{{name}}', tag['variable'], 1)
                trtdtdtd = trtdtdtd.replace('{{type}}', tag['type'].replace('\\',''), 1)
                trtdtdtd = trtdtdtd.replace('{{description}}', tag['description'], 1)
                param_tables += trtdtdtd
            if tag.name == 'tag' and tag['name'] == 'return':
                trtdtd = TEMPLATE_TRTDTD.replace('{{type}}', tag['type'], 1)
                trtdtd = trtdtd.replace('{{description}}', tag['description'], 1)
                return_tables += trtdtd
            if tag.name == 'tag' and tag['name'] == 'deprecated':
                m = m.replace('{{deprecated}}', 'class="deprecated"', 1)
                title = ''
                if tag['description'] == '':
                    title = 'НЕ РЕКОМЕНДУЕТСЯ'
                else:
                    title = tag['description']
                m = m.replace('{{title}}', title, 1)
        except AttributeError:
            pass
    if param_tables != '':
        t = TEMPLATE_PARAMETER_TABLE.replace('{{parameter_trtdtdtd}}', param_tables, 1)
        m = m.replace('{{parameter_table}}', t, 1)
    else:
        m = m.replace('{{parameter_table}}', '', 1)
    m = m.replace('{{parameters}}', param[0:-1].replace('\\',''), 1)
    if return_tables != '':
        t = TEMPLATE_RETURN_TABLE.replace('{{return_trtdtd}}', return_tables, 1)
        m = m.replace('{{return_table}}', t, 1)
    else:
        m = m.replace('{{return_table}}', '', 1)
    return m


for el in SOUP:
    try:
        _class = el.find('class')
        parameters = ''
        methods = ''
        for tag_in_class in _class.contents:
            try:
                p = ''
                if tag_in_class.name == 'property':
                    parameters += getProperty(tag_in_class)
                m = ''
                if tag_in_class.name == 'method':
                    methods += getMethod(tag_in_class)
            except AttributeError:
                pass
        object_name = _class.find('name').text
        object_description = _class.find('docblock').find('description').text
        LIS += TEMPLATE_LI.replace('{{text}}', object_name, 2)
        _object = TEMPLATE_OBJECT.replace('{{object_name}}', object_name, 1)
        _object = _object.replace('{{object_id}}', object_name, 1)
        _object = _object.replace('{{object_description}}', object_description, 1)
        _object = _object.replace('{{parameters}}', parameters, 1)
        _object = _object.replace('{{methods}}', methods, 1)
        OBJECTS += _object
    except AttributeError:
        pass

TEMPLATE_INDEX = TEMPLATE_INDEX.replace('{{menu_left_li}}', LIS, 1)
TEMPLATE_INDEX = TEMPLATE_INDEX.replace('{{title}}', SOUP['title'], 1)
TEMPLATE_INDEX = TEMPLATE_INDEX.replace('{{objects}}', OBJECTS, 1)

file_index = open("api.html", "w")
file_index.write(BeautifulSoup(TEMPLATE_INDEX, 'html.parser').prettify())
file_index.close()
