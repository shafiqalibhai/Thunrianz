<%@ Page Language="C#" debug="true" %>
<%@ import Namespace="System.Drawing" %>
<%@ import Namespace="System.Drawing.Imaging" %>
<%@ import Namespace="System.Text.RegularExpressions" %>
<script runat="server" language="c#">
/**
* @version $Id: thumb.aspx,v 1.2 2006/02/20 22:31:09 legolas558 Exp $
* @info .NET thumbnail wrapper for Lanius CMS Enhanced Gallery
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
*/
void Page_Load(Object sender, System.EventArgs e)
{
	string gallery_path = "../../../images/gallery/";
	try {
	
	string key = Request.QueryString["key"].ToString();
	if ((key.IndexOf("..")!=-1)||(key.IndexOf("/")!=-1))
		throw new Exception("Invalid key");
	
	string imgSource = System.IO.File.ReadAllText(Server.MapPath(gallery_path+key+".tmp"));
	
	System.Drawing.Image origImage = System.Drawing.Image.FromFile(imgSource);
	int max = Int32.Parse(Request.QueryString["max"].ToString());
	Response.ContentType = "image/jpeg";
	if ((origImage.Width<=max) && (origImage.Height<=max))
		origImage.Save(Response.OutputStream,System.Drawing.Imaging.ImageFormat.Jpeg);
	else {
		int new_w, new_h;
		if (origImage.Width <= origImage.Height) {
			new_h = max;
			new_w = (origImage.Width*max)/origImage.Height;
		} else {
			new_w = max;
			new_h = (origImage.Height*max)/origImage.Width;
		}
		System.Drawing.Image newImage = origImage.GetThumbnailImage(new_w,new_h, null, IntPtr.Zero);
		newImage.Save(Response.OutputStream, System.Drawing.Imaging.ImageFormat.Jpeg);
		newImage.Dispose();
	}
	origImage.Dispose();
    }
  catch (Exception ex)
    {
		Response.Status = "400 Bad Request";
		Response.Write("<h1>400 Bad Request</h1><pre>");
		string msg = ex.ToString();
		msg = Regex.Replace(msg, Regex.Escape(Server.MapPath("../../../")), "$D_ROOT\\");
		Response.Write(msg);
		Response.Write("</pre>");
		Response.End();
    }

} 
</script>