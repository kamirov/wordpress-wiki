classdef matlab_wiki
    % Static class that interacts with the Wordpress wiki
    
    properties (Constant, Access = private)
        % Options for querying the wiki
        opts = weboptions('Username', 'matlabber', ...
                          'Password', '<Enter the same password as in your server .htpasswd for this user>'),
        url = '<Put your url here>',
    end
    
    methods (Static)
        [status, response] = upload_file(filepath, article_title, title, description)  
        [status, response] = upload_image(fig, article_title, title, description, caption)
    end
    
    methods
        function obj = matlab_wiki()
        % Constructor 
            error('Don''t instantiate me. Call static methods using ''matlab_wiki.<method_name>''');
        end
    end
end

